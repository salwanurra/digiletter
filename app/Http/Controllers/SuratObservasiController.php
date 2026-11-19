<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SuratObservasi;
use App\Models\AnggotaObservasi;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratObservasiController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        abort_unless($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id, 403);
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = $userAuth;

            return view('pages.formsuratobservasi', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user()->load('mahasiswa');
        abort_unless($user->mahasiswa, 403);

        $data = new SuratObservasi();
        $data->nama_mhs = $user->name;
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;
        $data->tujuan_surat = $request->input('tujuan_surat');
        $data->tujuan_instansi = $request->input('tujuan_instansi');
        $data->domisili_instansi = $request->input('domisili_instansi');
        $data->mata_kuliah = $request->input('mata_kuliah');
        $data->judul_observasi = $request->input('judul_observasi');
        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Observasi_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.pdf';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Observasi';

        $data->save();

        if ($request->has('anggota')) {
            $data->anggota()->createMany($request->input('anggota'));
        }

        // Notify admins
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => $data->jenis_surat
            ]));
        }

        $outputPath = public_path('storage/surat-observasi/' . $filePath);
        $this->generatePdf($data, $outputPath);

        // Ensure the file exists before uploading
        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        try {
            $folderId = '1ON-N1NMNBdltBDc3uF2e3CAwTxdkOYAE';
            $googleDriveService = new GoogleDriveService();
            $fileId = $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        // return $pdf->stream('Surat_Observasi_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.pdf');
        return redirect()->back()->with('success', 'Surat Observasi telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generatePdf($data, $outputPath)
    {
        $ttdPimpinanDataIF = TtdPimpinan::where('prodi_pimpinan', 'Informatika')->get();
        $ttdPimpinanDataSI = TtdPimpinan::where('prodi_pimpinan', 'Sistem Informasi)')->get();

        $pdf = null;

        if ($data->prodi === 'Informatika') {
                $defaultTtdData =
                    [
                        'penanda_tangan' => 'a.n Dekan <br> Koord. Program Studi,',
                        'nama_pimpinan' => 'Carudin, S.Kom., M.Kom.',
                        'ttd_image' => 'terbaru/ttd_Carudin, S.Kom., M.Kom.png',
                        'nomor_induk' => 'NIP. 198804132022031002'
                    ];
                $pdf = PDF::loadView('template_surat.surat_observasi_if', compact('data', 'defaultTtdData'));
        } elseif ($data->prodi === 'Sistem Informasi') {
            if ($ttdPimpinanDataSI->isEmpty()) {
                $defaultTtdData = [
                    'penanda_tangan' => 'a.n Dekan <br> Koor. Program Studi,',
                    'nama_pimpinan' => 'Azhari Ali Ridha, S.Kom., M.M.S.I.',
                    'ttd_image' => 'ttd_si.png',
                    'nomor_induk' => 'NIDN. 0415098003'
                ];
                $pdf = PDF::loadView('template_surat.surat_observasi_si', compact('data', 'defaultTtdData'));
            } else {
                $pdf = PDF::loadView('template_surat.surat_observasi_si', compact('data', 'ttdPimpinanDataSI'));
            }
        }

        // Set paper size to F4
        $pdf->setPaper([0, 0, 595, 935], 'portrait');

        $pdf->save($outputPath);

        return $pdf;
    }

    public function riwayatSuratObservasi()
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratObservasi::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Observasi';

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat
            ]);
        }
    }

    public function setujuiSuratObservasi(Request $request, $id)
    {
        $SuratObservasi = SuratObservasi::findOrFail($id);

        $SuratObservasi->nomor_surat = $request->input('nomor_surat');
        $SuratObservasi->status = 'disetujui';
        $SuratObservasi->updated_at = now();
        $SuratObservasi->save();
        $outputPath = public_path('storage/surat-observasi/' . $SuratObservasi->file_path);

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratObservasi::where('nama_mhs', $SuratObservasi->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Observasi'
            ]));
        }

        // Update konten file PDF
        $this->generatePdf($SuratObservasi, $outputPath);

        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1ON-N1NMNBdltBDc3uF2e3CAwTxdkOYAE'; // Replace with your actual folder ID
            $googleDriveService = new GoogleDriveService();
            $googleDriveService->uploadFile($outputPath, $SuratObservasi->file_path, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Observasi telah disetujui!');
    }

    public function tidaksetujuSuratObservasi(Request $request, $id)
    {
        $SuratObservasi = SuratObservasi::findOrFail($id);
        $SuratObservasi->status = 'ditolak';
        $SuratObservasi->keterangan = $request->input('text_input');
        $SuratObservasi->save();

        return redirect()->back()->with('success', 'Surat Observasi telah ditolak!');
    }

    public function cancelsuratObservasi($id)
    {
        $SuratObservasi = SuratObservasi::find($id);
        $SuratObservasi->status = null;
        $SuratObservasi->keterangan = null;
        $SuratObservasi->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }


    public function downloadSuratObservasi($file_path)
    {
        $file = storage_path('app/public/surat-observasi/' . $file_path);

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            abort(404, 'File not found');
        }
    }
}
