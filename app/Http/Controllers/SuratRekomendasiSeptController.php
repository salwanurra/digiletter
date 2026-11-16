<?php

namespace App\Http\Controllers;

use App\Models\SuratRekomendasiSept;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\UserNotifcation;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDF;

class SuratRekomendasiSeptController extends Controller
{
    private const JENIS_SURAT = 'Surat Rekomendasi Mengikuti SEP-T';

    private const FOLDER = 'surat-rekomendasi-sept';

    public function create($id)
    {
        $userAuth = auth()->user();

        if ($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            return view('pages.formsuratrekomendasisept', [
                $navbarView,
                $sidebarView,
                'data' => $userAuth,
                'userAuth' => $userAuth,
            ]);
        }

        abort(403);
    }

    public function store(Request $request)
    {
        $request->validate([
            'semester' => 'required|in:I (Satu),II (Dua),III (Tiga),IV (Empat),V (Lima),VI (Enam),VII (Tujuh),VIII (Delapan),IX (Sembilan),X (Sepuluh),XI (Sebelas),XII (Dua Belas),XIII (Tiga Belas),XIV (Empat Belas)',
            'no_hp' => ['required', 'regex:/^[0-9]{8,15}$/'],
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:500',
        ], [
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester yang dipilih tidak valid.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP harus terdiri dari 8 sampai 15 digit angka.',
            'bukti_pembayaran.required' => 'Bukti telah membayar UKT wajib diunggah.',
            'bukti_pembayaran.image' => 'File yang diunggah harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'bukti_pembayaran.max' => 'Ukuran gambar tidak boleh melebihi 500 KB.',
        ]);

        $user = auth()->user()->load('mahasiswa');

        if (! $user->mahasiswa || ! $user->mahasiswa->prodi) {
            return redirect()->back()->withInput()->with('error', 'Program studi pada profil mahasiswa belum tersedia.');
        }

        $data = new SuratRekomendasiSept;
        $data->nama_mhs = Str::title($user->name);
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;
        $data->semester = $request->input('semester');
        $data->no_hp = $request->input('no_hp');
        $data->jenis_surat = self::JENIS_SURAT;

        $timestamp = now()->timestamp;
        $filePath = $timestamp.'_Surat_Rekomendasi_Mengikuti_SEP-T_'.Str::title($data->nama_mhs).'_'.$data->npm.'.pdf';
        $data->file_path = $filePath;
        $buktiPembayaran = $request->file('bukti_pembayaran');
        $imageName = $timestamp.'_ukt_'.$data->nama_mhs.'_'.$data->npm.'.'.$buktiPembayaran->extension();
        $data->bukti_pembayaran = $imageName;
        $data->save();

        $outputPath = public_path('storage/'.self::FOLDER.'/'.$filePath);
        $this->generatePdf($data, $outputPath);

        try {
            $googleDriveService = new GoogleDriveService;
            $folderId = $googleDriveService->getFolderId(self::FOLDER);
            $googleDriveService->uploadFile($outputPath, $filePath, $folderId);
            $googleDriveService->uploadFile($buktiPembayaran, $imageName, '1p_pb5PQIhosfuTsrhLi1bx2h7pW_fvJu');

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");

            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: '.$e->getMessage());
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification([
                'user_id' => $user->id,
                'name' => $user->name,
                'jenis_surat' => self::JENIS_SURAT,
            ]));
        }

        return redirect()->back()->with('success', 'Surat Rekomendasi Mengikuti SEP-T telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generatePdf(SuratRekomendasiSept $data, string $outputPath)
    {
        File::ensureDirectoryExists(dirname($outputPath));

        $pdf = PDF::loadView('template_surat.surat_rekomendasi_sept', compact('data'));
        $pdf->setPaper([0, 0, 595, 935], 'portrait');
        $pdf->save($outputPath);

        return $pdf;
    }

    public function setujuiSuratRekomendasiSept(Request $request, $id)
    {
        $request->validate(['nomor_surat' => 'required|string|max:255']);

        $surat = SuratRekomendasiSept::findOrFail($id);
        $surat->nomor_surat = $request->input('nomor_surat');
        $surat->status = 'disetujui';
        $surat->updated_at = now();
        $surat->save();

        $outputPath = public_path('storage/'.self::FOLDER.'/'.$surat->file_path);
        $this->generatePdf($surat, $outputPath);

        try {
            $googleDriveService = new GoogleDriveService;
            $folderId = $googleDriveService->getFolderId(self::FOLDER);
            $googleDriveService->uploadFile($outputPath, $surat->file_path, $folderId);

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");

            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: '.$e->getMessage());
        }

        $users = User::where('role', 'user')->where('npm', $surat->npm)->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'jenis_surat' => self::JENIS_SURAT,
            ]));
        }

        return redirect()->back()->with('success', 'Surat Rekomendasi Mengikuti SEP-T telah disetujui!');
    }

    public function tidaksetujuSuratRekomendasiSept(Request $request, $id)
    {
        $request->validate(['text_input' => 'required|string|max:255']);

        $surat = SuratRekomendasiSept::findOrFail($id);
        $surat->status = 'ditolak';
        $surat->keterangan = $request->input('text_input');
        $surat->save();

        return redirect()->back()->with('success', 'Surat Rekomendasi Mengikuti SEP-T telah ditolak!');
    }

    public function cancelsuratRekomendasiSept($id)
    {
        $surat = SuratRekomendasiSept::findOrFail($id);
        $surat->status = null;
        $surat->keterangan = null;
        $surat->save();

        session()->forget('data_approved_notifications');

        return redirect()->back();
    }

    public function riwayatSuratRekomendasiSept()
    {
        $userAuth = auth()->user();

        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));
            $data = SuratRekomendasiSept::where('npm', $userAuth->npm)->orderBy('created_at', 'desc')->get();

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => self::JENIS_SURAT,
                'userAuth' => $userAuth,
            ]);
        }

        abort(403);
    }
}
