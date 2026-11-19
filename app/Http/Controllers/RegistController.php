<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\CampusIdentityReconciliationService;
use App\Services\CampusStudentService;
use App\Services\VerificationEmailCooldown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class RegistController extends Controller
{
    public function index()
    {
        return view('auth/register', [
            'mahasiswa' => null,
        ]);
    }

    public function cekNPM(Request $request, CampusStudentService $campusStudents)
    {
        $validated = $request->validate([
            'npm' => ['required', 'digits:13'],
        ], [
            'npm.required' => 'NPM wajib diisi.',
            'npm.digits' => 'NPM harus terdiri dari 13 angka.',
        ]);

        $npm = $validated['npm'];

        try {
            $mahasiswa = $campusStudents->findByNim($npm);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if (! $mahasiswa) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'NPM tidak ditemukan pada data mahasiswa kampus.');
        }

        $prodi = $campusStudents->resolveProgramStudy($mahasiswa);

        if (! $prodi) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Program studi dari data kampus tidak dikenali. Silakan hubungi admin.');
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
                'email' => $campusStudents->resolveEmail($mahasiswa),
                'prodi' => $prodi,
            ],
        ]);
    }

    public function create(
        Request $request,
        CampusStudentService $campusStudents,
        CampusIdentityReconciliationService $reconciliation,
        VerificationEmailCooldown $emailCooldown
    ) {
        $validated = $request->validate([
            'npm' => ['required', 'digits:13', 'unique:users,npm'],
            'password' => ['required', 'min:6'],
        ], [
            'password.min' => 'Password harus memiliki minimal 6 karakter/angka.',
            'npm.unique' => 'NPM telah terdaftar.',
        ]);

        try {
            // Verifikasi ulang agar data tersembunyi dari form tidak dapat dimanipulasi.
            $mahasiswa = $campusStudents->findByNim($validated['npm']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if (! $mahasiswa) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'NPM tidak ditemukan pada data mahasiswa kampus.');
        }

        $email = $campusStudents->resolveEmail($mahasiswa);
        $prodi = $campusStudents->resolveProgramStudy($mahasiswa);

        if (! $prodi) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Program studi dari data kampus tidak dikenali. Silakan hubungi admin.');
        }

        if (User::where('email', $email)->exists()) {
            return redirect()
                ->route('regist')
                ->withInput()
                ->with('error', 'Email mahasiswa telah terdaftar. Silakan masuk menggunakan akun Anda.');
        }

        $user = DB::transaction(function () use ($validated, $mahasiswa, $email, $prodi, $reconciliation) {
            $user = User::create([
                'npm' => $validated['npm'],
                'name' => Str::title(trim($mahasiswa['nama_mahasiswa'])),
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ]);

            Mahasiswa::updateOrCreate(
                ['npm' => $validated['npm']],
                [
                    'user_id' => $user->id,
                    'prodi' => $prodi,
                    'foto' => 'user_profile.png',
                ]
            );

            $reconciliation->recordNewRegistration($user, $mahasiswa);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        try {
            $user->sendEmailVerificationNotification();
            $emailCooldown->markSent($user);
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
}
