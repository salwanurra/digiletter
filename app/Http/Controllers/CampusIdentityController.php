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
    public function show(Request $request)
    {
        return view('auth.campus-identity', [
            'verification' => $request->user()->campusIdentityVerification,
        ]);
    }

    public function submit(
        Request $request,
        CampusStudentService $campusStudents,
        CampusIdentityReconciliationService $reconciliation
    ) {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Password akun tidak sesuai.',
        ]);

        $user = $request->user();
        $npm = (string) $user->npm;

        if (! Str::of($npm)->isMatch('/^\d{13}$/')) {
            return back()->with('error', 'NPM pada akun tidak valid. Hubungi admin untuk memperbaiki data akun.');
        }

        try {
            $student = $campusStudents->findByNim($npm);

            if (! $student) {
                return back()->with('error', 'NPM akun tidak ditemukan pada data mahasiswa kampus. Hubungi admin untuk pemeriksaan.');
            }

            $verification = $reconciliation->submit($user, $student);
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'Identitas kampus tersebut sudah diklaim akun lain. Hubungi admin untuk pemeriksaan akun duplikat.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Layanan verifikasi mahasiswa sedang tidak tersedia. Silakan coba lagi.');
        }

        if ($verification->status === CampusIdentityVerification::STATUS_VERIFIED) {
            return redirect()->route('home')->with('success', 'Identitas kampus berhasil diverifikasi dan data resmi telah diterapkan.');
        }

        return redirect()->route('campus-identity.show')->with('error', 'Identitas belum dapat diverifikasi. Hubungi admin.');
    }
}
