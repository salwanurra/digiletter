<?php

namespace App\Http\Controllers;

use App\Models\CampusIdentityVerification;
use App\Services\CampusIdentityReconciliationService;
use App\Services\CampusStudentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class CampusIdentityController extends Controller
{
    public function show(Request $request, CampusStudentService $campusStudents)
    {
        $user = $request->user();
        $preview = null;

        if (Str::of((string) $user->npm)->isMatch('/^\d{13}$/')) {
            $preview = $this->previewFor($campusStudents, (string) $user->npm);
        }

        return view('auth.campus-identity', [
            'verification' => $user->campusIdentityVerification,
            'preview' => $preview,
        ]);
    }

    public function lookup(Request $request, CampusStudentService $campusStudents)
    {
        $validated = $request->validate(['npm' => ['required', 'digits:13']]);

        $preview = $this->previewFor($campusStudents, $validated['npm']);

        if ($preview === null) {
            return response()->json(['found' => false, 'message' => 'NPM tidak ditemukan pada data kampus.'], 404);
        }

        return response()->json(['found' => true] + $preview);
    }

    private function previewFor(CampusStudentService $campusStudents, string $npm): ?array
    {
        try {
            $student = $campusStudents->findByNim($npm);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (! $student) {
            return null;
        }

        return [
            'npm' => (string) $student['nim'],
            'name' => Str::title(trim((string) $student['nama_mahasiswa'])),
            'email' => $campusStudents->resolveEmail($student),
        ];
    }

    public function submit(
        Request $request,
        CampusStudentService $campusStudents,
        CampusIdentityReconciliationService $reconciliation
    ) {
        $request->validate([
            'npm' => ['required', 'digits:13'],
            'password' => ['required', 'current_password'],
        ], [
            'npm.required' => 'NPM wajib diisi.',
            'npm.digits' => 'NPM harus terdiri dari 13 angka.',
            'password.current_password' => 'Password akun tidak sesuai.',
        ]);

        $user = $request->user();
        $npm = (string) $request->input('npm');

        try {
            $student = $campusStudents->findByNim($npm);

            if (! $student) {
                return back()->with('error', 'NPM tidak ditemukan pada data mahasiswa kampus. Periksa kembali NPM Anda.');
            }

            $verification = $reconciliation->submit($user, $student);
        } catch (\DomainException $exception) {
            return back()->withInput($request->only('npm'))->with('error', $exception->getMessage());
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput($request->only('npm'))->with('error', 'Identitas kampus tersebut sudah diklaim akun lain. Hubungi admin untuk pemeriksaan akun duplikat.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if ($verification->status === CampusIdentityVerification::STATUS_VERIFIED) {
            if (! $user->fresh()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')->with('success', 'Data akun disesuaikan dengan database kampus. Silakan verifikasi email kampus Anda.');
            }

            return redirect()->route('home')->with('success', 'Identitas kampus berhasil diverifikasi dan data resmi telah diterapkan.');
        }

        return redirect()->route('campus-identity.show')->with('error', 'Identitas belum dapat diverifikasi. Hubungi admin.');
    }
}
