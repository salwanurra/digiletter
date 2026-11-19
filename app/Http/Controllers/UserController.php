<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\SuratBebasPustaka;
use App\Models\SuratIzinPenelitian;
use App\Models\SuratKeteranganAktif;
use App\Models\SuratKeteranganAktifOrtuPns;
use App\Models\SuratObservasi;
use App\Models\SuratPengajuanCuti;
use App\Models\SuratPengantarMagang;
use App\Models\SuratPengunduranDiri;
use App\Models\SuratPindahKuliah;
use App\Models\SuratRekomendasiSept;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function home()
    {
        $userAuth = auth()->user();

        $navbarView = view('layouts/navbar', compact('userAuth'));
        $sidebarView = view('layouts/sidebar', compact('userAuth'));

        return view('pages.home', compact('navbarView', 'sidebarView', 'userAuth'));
    }

    public function profile($id)
    {
        $user = $this->userForRoute($id)->load('mahasiswa');
        $userAuth = $user;
        $navbarView = view('layouts/navbar', compact('userAuth'));
        $sidebarView = view('layouts/sidebar', compact('userAuth'));
        $createdAt = $user->created_at;

        return view('pages.profile', compact('navbarView', 'sidebarView', 'user', 'userAuth', 'createdAt'));
    }

    public function lengkapiProfile(Request $request, $id)
    {
        $user = $this->userForRoute($id)->load('mahasiswa');

        $request->validate([
            'semester' => ['nullable', 'string', 'max:30'],
            'prodi' => ['nullable', 'in:Informatika,Sistem Informasi'],
            'domisili' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'no_hp' => ['required', 'regex:/^[0-9]{8,15}$/'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:500'],
        ], [
            'domisili.required' => 'Domisili harus diisi',
            'no_hp.required' => 'Nomor HP harus diisi',
            'no_hp.regex' => 'Nomor HP harus terdiri dari 8 sampai 15 angka.',
            'foto.max' => 'Ukuran gambar tidak boleh melebihi 500 KB.',
        ]);

        // Upload gambar baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            $namaFile = Str::slug($user->name).'-'.$user->npm.'-'.Str::random(12).'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/foto-mahasiswa', $namaFile);
        } else {
            $namaFile = null;
        }

        // Ambil foto sebelumnya
        $previousFoto = $user->mahasiswa?->foto;

        // Tetapkan foto sebelumnya jika upload baru bernilai null
        $namaFile = $namaFile ?? $previousFoto;

        $data = [
            'user_id' => $user->id,
            'semester' => $request->input('semester'),
            'prodi' => $user->mahasiswa?->prodi,
            'domisili' => $request->input('domisili'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'no_hp' => $request->input('no_hp'),
            'status' => $user->mahasiswa?->status,
            'foto' => $namaFile ?? 'user_profile.png',
        ];

        Mahasiswa::updateOrCreate(['user_id' => $user->id], $data);

        return redirect()->route('user.profile', ['id' => $user->id])->with('success', 'Data diri telah berhasil diubah!');
    }

    public function settingAccount($id)
    {
        $user = $this->userForRoute($id)->load('mahasiswa');
        $userAuth = $user;
        $navbarView = view('layouts/navbar', compact('userAuth'));
        $sidebarView = view('layouts/sidebar', compact('userAuth'));

        return view('pages.settingAccount', compact('navbarView', 'sidebarView', 'user', 'userAuth'));
    }

    public function settingSecurity($id)
    {
        $userAuth = $this->userForRoute($id)->load('mahasiswa');
        $navbarView = view('layouts/navbar', compact('userAuth'));
        $sidebarView = view('layouts/sidebar', compact('userAuth'));

        return view('pages.settingSecurity', compact('navbarView', 'sidebarView', 'userAuth'));
    }

    public function changePassword(Request $request, $id)
    {
        $user = $this->userForRoute($id);

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi Password baru tidak cocok.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

    public function markAsReadApprove($id)
    {
        if ($id) {
            auth()->user()->notifications->where('id', $id)->markAsRead();
        }

        return redirect()->back();
    }

    public function downloadSurat($folders, $file_path)
    {
        $modelsByFolder = [
            'surat-izin-penelitian' => SuratIzinPenelitian::class,
            'surat-keterangan-aktif' => SuratKeteranganAktif::class,
            'surat-keterangan-aktif-ortu-pns' => SuratKeteranganAktifOrtuPns::class,
            'surat-bebas-pustaka' => SuratBebasPustaka::class,
            'surat-pengajuan-cuti' => SuratPengajuanCuti::class,
            'surat-pengunduran-diri' => SuratPengunduranDiri::class,
            'surat-observasi' => SuratObservasi::class,
            'surat-pindah-kuliah' => SuratPindahKuliah::class,
            'surat-pengantar-magang' => SuratPengantarMagang::class,
            'surat-rekomendasi-sept' => SuratRekomendasiSept::class,
        ];

        abort_unless(isset($modelsByFolder[$folders]), 404);

        $mayDownload = $modelsByFolder[$folders]::query()
            ->where('npm', auth()->user()->npm)
            ->where('file_path', $file_path)
            ->where('status', 'disetujui')
            ->exists();

        abort_unless($mayDownload, 403);

        try {
            // Instantiate the Google Drive service
            $googleDriveService = new GoogleDriveService();
    
            // Resolve the folder ID
            $folderId = $googleDriveService->getFolderId($folders);
    
            // Get the file ID by name and folder
            $fileId = $googleDriveService->getFileIdByName($file_path, $folderId);
    
            if (!$fileId) {
                abort(404, 'File not found in Google Drive');
            }
    
            // Download the file content
            $fileContent = $googleDriveService->downloadFile($fileId);
            
            $extension = pathinfo($file_path, PATHINFO_EXTENSION);
            $mimeTypes = [
                'pdf'  => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
            return response($fileContent, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $file_path . '"');
        } catch (\Exception $e) {
            \Log::error("Error downloading file: " . $e->getMessage());
            abort(500, 'Error downloading file from Google Drive');
        }
    }

    public function userGuide($id)
    {
        $userAuth = $this->userForRoute($id)->load('mahasiswa');
        $navbarView = view('layouts/navbar', compact('userAuth'));
        $sidebarView = view('layouts/sidebar', compact('userAuth'));

        return view('pages.userGuide', compact('navbarView', 'sidebarView', 'userAuth'));
    }

    private function userForRoute($id): User
    {
        $user = auth()->user();

        abort_unless($user && (int) $user->getKey() === (int) $id, 403);

        return $user;
    }

}
