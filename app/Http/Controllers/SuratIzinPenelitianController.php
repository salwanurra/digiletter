<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SuratIzinPenelitian;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratIzinPenelitianController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        abort_unless($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id, 403);
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = $userAuth;

            return view('pages.formsuratizinpenelitian', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user()->load('mahasiswa');
        abort_unless($user->mahasiswa, 403);

        $data = new SuratIzinPenelitian();
        $data->nama_mhs = Str::title($user->name);
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;
        $data->lingkup = $request->input('lingkup');
        $data->semester = $request->input('semester');
        $data->tujuan_surat = $request->input('tujuan_surat');
        $data->tujuan_instansi = $request->input('tujuan_instansi');
        $data->domisili_instansi = $request->input('domisili_instansi');
        $data->judul_penelitian = $request->input('judul_penelitian');

        $timestamp = now()->timestamp;
        $fileExtension = $data->lingkup === 'Internal' ? 'docx' : 'pdf';
        $fileName = $timestamp . '_Surat_Izin_Penelitian_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.' . $fileExtension;
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Izin Penelitian';

        $data->save();

        // Notify admins
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => $data->jenis_surat
            ]));
        }

        $outputPath = public_path('storage/surat-izin-penelitian/' . $filePath);

        if ($data->lingkup === 'Internal') {
            $this->generateWord($data, $outputPath);
        }elseif ($data->lingkup === 'Eksternal') {
            $this->generatePdf($data, $outputPath);
        }

        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        try {
            $folderId = '1w-1ruM9tMAwX6ne9z55e_49jfCEpcIro';
            $googleDriveService = new GoogleDriveService();
            $fileId = $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Izin Penelitian telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generateWord($data, $outputPath)
    {
        // pembuatan docx
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('Surat-Izin-Penelitian.docx');
        $templateProcessor->setValue('nama_mhs', $data->nama_mhs);
        $templateProcessor->setValue('npm', $data->npm);
        $templateProcessor->setValue('prodi', $data->prodi);
        $templateProcessor->setValue('judul_penelitian', $data->judul_penelitian);
        $templateProcessor->setValue('created_at',  \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D MMMM Y'));

        $wordDoc = $templateProcessor->saveAs($outputPath);
        return $wordDoc;
    }


    private function generatePdf($data, $outputPath)
    {
        $ttdPimpinanDataIF = TtdPimpinan::where('prodi_pimpinan', 'Informatika')->get();
        $ttdPimpinanDataSI = TtdPimpinan::where('prodi_pimpinan', 'Sistem Informasi')->get();

        $pdf = null;

        if ($data->prodi === 'Informatika') {
            if ($ttdPimpinanDataIF->isEmpty()) {
                $defaultTtdData =
                    [
                        'penanda_tangan' => 'a.n Dekan <br> Koord. Program Studi,',
                        'nama_pimpinan' => 'E. Haodudin Nurkifli, S.T., M.Cs., Ph.D.',
                        'ttd_image' => 'ttd_if.png',
                        'nomor_induk' => 'NIP. 198504032021211003'
                    ];  
                $pdf = PDF::loadView('template_surat.surat_izin_penelitian_if', compact('data', 'defaultTtdData'));
            } else {
                $pdf = PDF::loadView('template_surat.surat_izin_penelitian_if', compact('data', 'ttdPimpinanDataIF'));
            }
        } elseif ($data->prodi === 'Sistem Informasi') {
            if ($ttdPimpinanDataSI->isEmpty()) {
                $defaultTtdData = [
                    'penanda_tangan' => 'a.n Dekan <br> Koor.  Program Studi,',
                    'nama_pimpinan' => 'Azhari Ali Ridha, S.Kom., M.M.S.I.',
                    'ttd_image' => 'ttd_si.png',
                    'nomor_induk' => 'NIDN. 0415098003'
                ];
                $pdf = PDF::loadView('template_surat.surat_izin_penelitian_si', compact('data', 'defaultTtdData'));
            } else {
                $pdf = PDF::loadView('template_surat.surat_izin_penelitian_si', compact('data', 'ttdPimpinanDataSI'));
            }
        }

        // Set paper size to F4
        $pdf->setPaper([0, 0, 595, 935], 'portrait');

        $pdf->save($outputPath);

        return $pdf;
    }

    public function setujuiSuratIzinPenelitian(Request $request, $id)
    {
        $SuratIzinPenelitian = SuratIzinPenelitian::findOrFail($id);
        $SuratIzinPenelitian->nomor_surat = $request->input('nomor_surat');
        $SuratIzinPenelitian->status = 'disetujui';
        $SuratIzinPenelitian->updated_at = now();
        $SuratIzinPenelitian->save();
        
        $outputPath = public_path('storage/surat-izin-penelitian/' . $SuratIzinPenelitian->file_path);
        

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratIzinPenelitian::where('nama_mhs', $SuratIzinPenelitian->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Izin Penelitian'
            ]));
        }

        // Update konten file PDF
        if ($SuratIzinPenelitian->lingkup == 'Eksternal') {
            // Update konten file PDF
            $this->generatePdf($SuratIzinPenelitian, $outputPath);

            try {
                // Folder ID where you want to upload the file (ensure it's shared with the service account)
                $folderId = '1w-1ruM9tMAwX6ne9z55e_49jfCEpcIro'; // Replace with your actual folder ID
                $googleDriveService = new GoogleDriveService();
                $googleDriveService->uploadFile($outputPath, $SuratIzinPenelitian->file_path, $folderId);

                // Delete the temporary local file after uploading
                if (file_exists($outputPath)) {
                    unlink($outputPath);
                }
            } catch (\Exception $e) {
                \Log::error("Error uploading file: {$e->getMessage()}");
                return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Surat Izin Penelitian telah disetujui!');
    }

    public function tidaksetujuSuratIzinPenelitian(Request $request, $id)
    {
        $SuratIzinPenelitian = SuratIzinPenelitian::findOrFail($id);
        $SuratIzinPenelitian->status = 'ditolak';
        $SuratIzinPenelitian->keterangan = $request->input('text_input');
        $SuratIzinPenelitian->save();

        return redirect()->back()->with('success', 'Surat Izin Penelitian telah ditolak!');
    }

    public function cancelsuratIzinPenelitian($id)
    {
        $SuratIzinPenelitian = SuratIzinPenelitian::find($id);
        $SuratIzinPenelitian->status = null;
        $SuratIzinPenelitian->keterangan = null;
        $SuratIzinPenelitian->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }

    public function riwayatSuratIzinPenelitian(Request $request)
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratIzinPenelitian::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Izin Penelitian';

            // Menggunakan ucfirst untuk mengubah huruf pertama menjadi besar
            $formattedData = $data->map(function ($item) {
                $item->nama_mhs = ucfirst($item->nama_mhs);
                $item->judul_penelitian = ucfirst($item->judul_penelitian);
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

    public function downloadSuratIzinPenelitian($file_path)
    {
        $file = Gdrive::get('surat-izin-penelitian/' . $file_path);

        if (file_exists($file)) {
            return response($file->file, 200);
        } else {
            abort(404, 'File not found');
        }
    }
}
