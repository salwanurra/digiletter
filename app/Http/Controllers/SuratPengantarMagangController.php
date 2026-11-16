<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SuratPengantarMagang;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratPengantarMagangController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = User::where('id', $id)->first();

            return view('pages.formsuratpengantarmagang', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        $buktiPembayaran = $request->file('bukti_pembayaran');
        $request->validate([
            'bukti_pembayaran' => 'required|image|max:500',
        ], [
            'bukti_pembayaran.required' => 'Bukti telah membayar UKT wajib diunggah.',
            'bukti_pembayaran.image' => 'File yang diunggah harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'bukti_pembayaran.max' => 'Ukuran gambar tidak boleh melebihi 500 KB.',
        ]);

        $data = new SuratPengantarMagang();
        $data->nama_mhs = Str::title($request->input('nama_mhs'));
        $data->npm = $request->input('npm');
        $data->prodi = $request->input('prodi');
        $data->email = $request->input('email');
        $data->no_hp = $request->input('no_hp');
        $data->tempat_lahir = $request->input('tempat_lahir');
        $data->tanggal_lahir = $request->input('tanggal_lahir');
        $data->agama = $request->input('agama');
        $data->alamat = $request->input('alamat');
        $data->tujuan_surat = $request->input('tujuan_surat');
        $data->tujuan_instansi = $request->input('tujuan_instansi');
        $data->domisili_instansi = $request->input('domisili_instansi');

        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Pengantar_Magang_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.pdf';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Pengantar Magang';
        
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

        $outputPath = public_path('storage/surat-pengantar-magang/' . $filePath);
        $this->generatePdf($data, $outputPath);

        // return $pdf->stream();

        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        try {
            $folderId = '1MJ8oavzSqAJx6ATmRTsm2nWYQeFhNFGO';
            $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Pengantar Magang telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }


    private function generatePdf($data, $outputPath)
    {
        $ttdPimpinanDataIF = TtdPimpinan::where('prodi_pimpinan', 'Informatika')->get();
        $ttdPimpinanDataSI = TtdPimpinan::where('prodi_pimpinan', 'Sistem Informasi')->get();

        $pdf = null;

        if ($data->prodi === 'Informatika') {
            $defaultTtdData =
                [
                    'penanda_tangan' => 'a.n Dekan <br> Koordinator Program Studi,',
                    'nama_pimpinan' => 'Carudin, M.Kom.',
                    'ttd_image' => 'terbaru/ttd_Carudin, S.Kom., M.Kom.png',
                    'nomor_induk' => 'NIP. 198804132022031002'
                ];  
            $pdf = PDF::loadView('template_surat.surat_pengantar_magang_if', compact('data', 'defaultTtdData'));
        } elseif ($data->prodi === 'Sistem Informasi') {
            if ($ttdPimpinanDataSI->isEmpty()) {
                $defaultTtdData = [
                    'penanda_tangan' => 'a.n Dekan <br> Koordinator Program Studi,',
                    'nama_pimpinan' => 'Azhari Ali Ridha, S.Kom., M.M.S.I.',
                    'ttd_image' => 'ttd_si.png',
                    'nomor_induk' => 'NIDN. 0415098003'
                ];
                $pdf = PDF::loadView('template_surat.surat_pengantar_magang_si', compact('data', 'defaultTtdData'));
            } else {
                $pdf = PDF::loadView('template_surat.surat_pengantar_magang_si', compact('data', 'ttdPimpinanDataSI'));
            }
        }

        // Set paper size to F4
        $pdf->setPaper([0, 0, 595, 935], 'portrait');

        $pdf->save($outputPath);

        return $pdf;
    }

    public function setujuiSuratPengantarMagang(Request $request, $id)
    {
        $SuratPengantarMagang = SuratPengantarMagang::findOrFail($id);
        $SuratPengantarMagang->nomor_surat = $request->input('nomor_surat');
        $SuratPengantarMagang->status = 'disetujui';
        $SuratPengantarMagang->updated_at = now();
        $SuratPengantarMagang->save();
        
        $outputPath = public_path('storage/surat-pengantar-magang/' . $SuratPengantarMagang->file_path);
        

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratPengantarMagang::where('nama_mhs', $SuratPengantarMagang->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Pengantar Magang'
            ]));
        }

        $this->generatePdf($SuratPengantarMagang, $outputPath);

        try {
            $folderId = '1MJ8oavzSqAJx6ATmRTsm2nWYQeFhNFGO';
            $googleDriveService = new GoogleDriveService();
            $googleDriveService->uploadFile($outputPath, $SuratPengantarMagang->file_path, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Pengantar Magang telah disetujui!');
    }

    public function tidaksetujuSuratPengantarMagang(Request $request, $id)
    {
        $SuratPengantarMagang = SuratPengantarMagang::findOrFail($id);
        $SuratPengantarMagang->status = 'ditolak';
        $SuratPengantarMagang->keterangan = $request->input('text_input');
        $SuratPengantarMagang->save();

        return redirect()->back()->with('success', 'Surat Pengantar Magang telah ditolak!');
    }

    public function cancelsuratPengantarMagang($id)
    {
        $SuratPengantarMagang = SuratPengantarMagang::find($id);
        $SuratPengantarMagang->status = null;
        $SuratPengantarMagang->keterangan = null;
        $SuratPengantarMagang->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }

    public function riwayatSuratPengantarMagang(Request $request)
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratPengantarMagang::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Pengantar Magang';

            // Menggunakan ucfirst untuk mengubah huruf pertama menjadi besar
            $formattedData = $data->map(function ($item) {
                $item->nama_mhs = ucfirst($item->nama_mhs);
                return $item;
            });

            return view('pages.riwayatsurat', [
                'data' => $formattedData,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat,
                'userAuth'
            ]);
        }
    }

    public function downloadSuratPengantarMagang($file_path)
    {
        $file = Gdrive::get('surat-pengantar-magang/' . $file_path);

        if (file_exists($file)) {
            return response($file->file, 200);
        } else {
            abort(404, 'File not found');
        }
    }
}