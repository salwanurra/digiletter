<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Throwable;

class RegistController extends Controller
{
    public function index()
    {
        return view('auth/register', [
            'mahasiswa' => null,
        ]);
    }

    public function cekNPM(Request $request)
    {
        $validated = $request->validate([
            'npm' => ['required', 'digits:13'],
        ], [
            'npm.required' => 'NPM wajib diisi.',
            'npm.digits' => 'NPM harus terdiri dari 13 angka.',
        ]);

        $npm = $validated['npm'];

        try {
            $mahasiswa = $this->findMahasiswaInApi($npm);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if (!$mahasiswa) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'NPM tidak ditemukan pada data mahasiswa kampus.');
        }

        if (User::where('npm', $npm)->exists()) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'NPM telah terdaftar. Silakan masuk menggunakan akun Anda.');
        }

        return view('auth/register', [
            'mahasiswa' => [
                'npm' => (string) $mahasiswa['nim'],
                'nama_mahasiswa' => $mahasiswa['nama_mahasiswa'],
                'email' => $this->resolveStudentEmail($mahasiswa),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'npm' => ['required', 'digits:13', 'unique:users,npm'],
            'password' => ['required', 'min:6'],
        ], [
            'password.min' => 'Password harus memiliki minimal 6 karakter/angka.',
            'npm.unique' => 'NPM telah terdaftar.',
        ]);

        try {
            // Verifikasi ulang agar data tersembunyi dari form tidak dapat dimanipulasi.
            $mahasiswa = $this->findMahasiswaInApi($validated['npm']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if (!$mahasiswa) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'NPM tidak ditemukan pada data mahasiswa kampus.');
        }

        $email = $this->resolveStudentEmail($mahasiswa);

        if (User::where('email', $email)->exists()) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Email mahasiswa telah terdaftar. Silakan masuk menggunakan akun Anda.');
        }

        $user = DB::transaction(function () use ($validated, $mahasiswa, $email) {
            $user = User::create([
                'npm' => $validated['npm'],
                'name' => Str::title(trim($mahasiswa['nama_mahasiswa'])),
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ]);

            Mahasiswa::updateOrCreate(
                ['npm' => $validated['npm']],
                ['user_id' => $user->id, 'foto' => 'user_profile.png']
            );

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('verification.notice')
                ->with('warning', 'Akun berhasil dibuat, tetapi email verifikasi belum dapat dikirim. Silakan kirim ulang.');
        }

        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }

    private function findMahasiswaInApi(string $nim): ?array
    {
        $baseUrl = rtrim((string) config('services.kampus.base_url'), '/');
        $token = (string) config('services.kampus.token');

        if ($baseUrl === '' || $token === '') {
            throw new \RuntimeException('Konfigurasi API kampus belum lengkap.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->timeout(15)
            ->get($baseUrl . '/mahasiswa/nim/' . urlencode($nim));

        if ($response->notFound()) {
            return null;
        }

        $response->throw();

        $data = $response->json('data');
        $mahasiswa = is_array($data) && array_is_list($data)
            ? ($data[0] ?? null)
            : $data;

        if (!is_array($mahasiswa)
            || (string) ($mahasiswa['nim'] ?? '') !== $nim
            || trim((string) ($mahasiswa['nama_mahasiswa'] ?? '')) === '') {
            return null;
        }

        return $mahasiswa;
    }

    private function resolveStudentEmail(array $mahasiswa): string
    {
        $email = strtolower(trim((string) ($mahasiswa['email'] ?? '')));

        return filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : $mahasiswa['nim'] . '@student.unsika.ac.id';
    }
}
