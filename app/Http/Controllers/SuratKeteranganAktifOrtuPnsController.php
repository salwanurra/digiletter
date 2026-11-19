<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SuratKeteranganAktifOrtuPns;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use App\Services\GoogleDriveService;

class SuratKeteranganAktifOrtuPnsController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        abort_unless($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id, 403);
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = $userAuth;

            return view('pages.formsuratketeranganaktifortupns', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user()->load('mahasiswa');
        abort_unless($user->mahasiswa, 403);

        $buktiPembayaran = $request->file('bukti_pembayaran');
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:500',
        ], [
            'bukti_pembayaran.required' => 'Bukti telah membayar UKT wajib diunggah.',
            'bukti_pembayaran.image' => 'File yang diunggah harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'bukti_pembayaran.max' => 'Ukuran gambar tidak boleh melebihi 500 KB.',
        ]);

        $data = new SuratKeteranganAktifOrtuPns();

        // mahasiswa
        $data->nama_mhs = Str::title($user->name);
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;
        $data->semester = $request->input('semester');
        $data->alamat = Str::title($request->input('alamat'));
        $data->periode = $request->input('periode');
        $data->tahun_akademik = $request->input('tahun_akademik');

        // ortu
        $data->nama_ortu = Str::title($request->input('nama_ortu'));
        $data->nomor_induk_ortu = $request->input('nomor_induk_ortu');
        $data->jabatan_ortu = Str::title($request->input('jabatan_ortu'));
        $data->instansi = Str::title($request->input('instansi'));

        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Keterangan_Aktif_Kuliah_Ortu_PNS_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.pdf';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Keterangan Aktif Kuliah Ortu PNS';

        // img Bukti telah membayar UKT
        $imageName = $timestamp . '_' . 'ukt' . '_' . $data->nama_mhs . '_' . $data->npm . '.' . $buktiPembayaran->extension();
        $data->bukti_pembayaran = $imageName;
        $data->save();

        $googleDriveService = new GoogleDriveService();

        $googleDriveService->uploadFile($buktiPembayaran, $imageName, "1p_pb5PQIhosfuTsrhLi1bx2h7pW_fvJu");

        // Notify admins
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => $data->jenis_surat
            ]));
        }

        $outputPath = public_path('storage/surat-keterangan-aktif-ortu-pns/' . $filePath);
        $this->generatePdf($data, $outputPath);
        
        // Ensure the file exists before uploading
        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        // Upload to Google Drive
        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1fcxX-JFCfw3YMEHJ8wkMRMQqOulKKkF2'; // Replace with your actual folder ID
            $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Keterangan Aktif Ortu PNS telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generatePdf($data, $outputPath)
    {
        $ttdPimpinanDataWadek = TtdPimpinan::where('prodi_pimpinan', 'Wadek')->get();
        $ttdPimpinanDataIF = TtdPimpinan::where('prodi_pimpinan', 'Informatika')->get();

        $pdf = null;

        if ($ttdPimpinanDataWadek->isEmpty()) {
            if ($ttdPimpinanDataIF->isEmpty()) {
                $defaultTtdData =
                    [
                        'penanda_tangan' => 'a.n Dekan <br> Koord. Program Studi,',
                        'nama_pimpinan' => 'E. Haodudin Nurkifli, S.T., M.Cs., Ph.D.',
                        'ttd_image' => 'ttd_if.png',
                        'nomor_induk' => 'NIP. 198504032021211003',
                        'nomor_induk_only' => '198504032021211003'
                    ];  
                $pdf = PDF::loadView('template_surat.surat_keterangan_aktif_ortu_pns', compact('data', 'defaultTtdData'));
            } else {
                $nomor_induk_only = preg_replace('/\D/', '', $ttdPimpinanDataIF[0]->nomor_induk);
                $pdf = PDF::loadView('template_surat.surat_keterangan_aktif_ortu_pns', compact('data', 'ttdPimpinanDataIF', 'nomor_induk_only'));
            }
        } else {
            $pdf = PDF::loadView('template_surat.surat_keterangan_aktif_ortu_pns', compact('data', 'ttdPimpinanDataWadek'));
        }

        // Set paper size to F4
        $pdf->setPaper([0, 0, 595, 935], 'portrait');

        $pdf->save($outputPath);

        return $pdf;
    }

    public function riwayatSuratKeteranganAktifOrtuPns()
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratKeteranganAktifOrtuPns::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Keterangan Aktif Kuliah Ortu PNS';

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat
            ]);
        }
    }

    public function setujuiSuratKeteranganAktifOrtuPns(Request $request, $id)
    {
        $SuratKeteranganAktifOrtuPns = SuratKeteranganAktifOrtuPns::findOrFail($id);

        $SuratKeteranganAktifOrtuPns->nomor_surat = $request->input('nomor_surat');
        $SuratKeteranganAktifOrtuPns->status = 'disetujui';
        $SuratKeteranganAktifOrtuPns->updated_at = now();
        $SuratKeteranganAktifOrtuPns->save();
        $outputPath = public_path('storage/surat-keterangan-aktif-ortu-pns/' . $SuratKeteranganAktifOrtuPns->file_path);


        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratKeteranganAktifOrtuPns::where('nama_mhs', $SuratKeteranganAktifOrtuPns->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Keterangan Aktif Kuliah Ortu PNS'
            ]));
        }

        // Update konten file PDF
        $this->generatePdf($SuratKeteranganAktifOrtuPns, $outputPath);
        
        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1fcxX-JFCfw3YMEHJ8wkMRMQqOulKKkF2'; // Replace with your actual folder ID
            $googleDriveService = new GoogleDriveService();
            $googleDriveService->uploadFile($outputPath, $SuratKeteranganAktifOrtuPns->file_path, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Keterangan Aktif Kuliah Ortu PNS telah disetujui!');
    }

    public function tidaksetujuSuratKeteranganAktifOrtuPns(Request $request, $id)
    {
        $SuratKeteranganAktifOrtuPns = SuratKeteranganAktifOrtuPns::findOrFail($id);
        $SuratKeteranganAktifOrtuPns->status = 'ditolak';
        $SuratKeteranganAktifOrtuPns->keterangan = $request->input('text_input');
        $SuratKeteranganAktifOrtuPns->save();

        return redirect()->back()->with('success', 'Surat Keterangan Aktif Kuliah Ortu PNS telah ditolak!');
    }

    public function cancelsuratKeteranganAktifOrtuPns($id)
    {
        $SuratKeteranganAktifOrtuPns = SuratKeteranganAktifOrtuPns::find($id);
        $SuratKeteranganAktifOrtuPns->status = null;
        $SuratKeteranganAktifOrtuPns->keterangan = null;
        $SuratKeteranganAktifOrtuPns->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }


    public function downloadSuratKeteranganAktifOrtuPns($file_path)
    {
        $file = storage_path('app/public/surat-keterangan-aktif-ortu-pns/' . $file_path);

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            abort(404, 'File not found');
        }
    }
}
