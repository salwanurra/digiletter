<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SuratBebasPustaka;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratBebasPustakaController extends Controller
{

    public function create($id)
    {
        $userAuth = auth()->user();
        abort_unless($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id, 403);
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = $userAuth;

            return view('pages.formsuratbebaspustaka', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user()->load('mahasiswa');
        abort_unless($user->mahasiswa, 403);

        $data = new SuratBebasPustaka();
        $data->nama_mhs = Str::title($user->name);
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;

        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Bebas_Pustaka_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.pdf';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Bebas Pustaka';

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

        $outputPath = public_path('storage/surat-bebas-pustaka/' . $filePath);
        $this->generatePdf($data, $outputPath);

        // Ensure the file exists before uploading
        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

       // Upload to Google Drive
        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1llk8yjHJSE11jWZudisCKwhU1NwvF2Rg'; // Replace with your actual folder ID
            $googleDriveService = new GoogleDriveService();
            $fileId = $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Bebas Pustaka telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generatePdf($data, $outputPath)
    {
        $ttdPimpinanDataIF = TtdPimpinan::where('prodi_pimpinan', 'IF (Surat Bebas Pustaka)')->get();
        $ttdPimpinanDataSI = TtdPimpinan::where('prodi_pimpinan', 'SI (Surat Bebas Pustaka)')->get();

        $pdf = null;

        if ($data->prodi === 'Informatika') {
            if ($ttdPimpinanDataIF->isEmpty()) {
                $defaultTtdData =
                    [
                        'penanda_tangan' => 'a.n Dekan <br> Koordinator Program Studi Informatika,',
                        'nama_pimpinan' => 'Carudin, S.Kom., M.Kom.',
                        'ttd_image' => 'ttd_carudin.png',
                        'nomor_induk' => 'NIP. 198804132022031002'
                    ];
                $pdf = PDF::loadView('template_surat.surat_bebas_pustaka_if', compact('data', 'defaultTtdData'));
            } 
            else {
                $pdf = PDF::loadView('template_surat.surat_bebas_pustaka_if', compact('data', 'ttdPimpinanDataIF'));
            }
        } elseif ($data->prodi === 'Sistem Informasi') {
            if ($ttdPimpinanDataSI->isEmpty()) {
                $defaultTtdData = [
                    'penanda_tangan' => 'a.n Dekan <br> Koordinator Program Studi Sistem Informasi',
                    'nama_pimpinan' => 'Azhari Ali Ridha, S.Kom., M.M.S.I.',
                    'ttd_image' => 'ttd_azhari.png',
                    'nomor_induk' => 'NIDN. 0415098003'
                ];
                $pdf = PDF::loadView('template_surat.surat_bebas_pustaka_si', compact('data', 'defaultTtdData'));
            } else {
                $pdf = PDF::loadView('template_surat.surat_bebas_pustaka_si', compact('data', 'ttdPimpinanDataSI'));
            }
        }

        // Set paper size to F4
        $pdf->setPaper([0, 0, 595, 935], 'portrait');

        $pdf->save($outputPath);

        return $pdf;
    }

    public function riwayatSuratBebasPustaka()
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratBebasPustaka::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Bebas Pustaka';

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat
            ]);
        }
    }

    public function setujuiSuratBebasPustaka(Request $request, $id)
    {
        $SuratBebasPustaka = SuratBebasPustaka::findOrFail($id);

        $SuratBebasPustaka->nomor_surat = $request->input('nomor_surat');
        $SuratBebasPustaka->status = 'disetujui';
        $SuratBebasPustaka->updated_at = now();
        $SuratBebasPustaka->save();
        $outputPath = public_path('storage/surat-bebas-pustaka/' . $SuratBebasPustaka->file_path);

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratBebasPustaka::where('nama_mhs', $SuratBebasPustaka->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Bebas Pustaka'
            ]));
        }

        // Update konten file PDF
        $this->generatePdf($SuratBebasPustaka, $outputPath);

        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1llk8yjHJSE11jWZudisCKwhU1NwvF2Rg'; // Replace with your actual folder ID
            $googleDriveService = new GoogleDriveService();
            $googleDriveService->uploadFile($outputPath, $SuratBebasPustaka->file_path, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Bebas Pustaka telah disetujui!');
    }

    public function tidaksetujuSuratBebasPustaka(Request $request, $id)
    {
        $SuratBebasPustaka = SuratBebasPustaka::findOrFail($id);
        $SuratBebasPustaka->status = 'ditolak';
        $SuratBebasPustaka->keterangan = $request->input('text_input');
        $SuratBebasPustaka->save();

        return redirect()->back()->with('success', 'Surat Bebas Pustaka telah ditolak!');
    }

    public function cancelsuratBebasPustaka($id)
    {
        $SuratBebasPustaka = SuratBebasPustaka::find($id);
        $SuratBebasPustaka->status = null;
        $SuratBebasPustaka->keterangan = null;
        $SuratBebasPustaka->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }


    public function downloadSuratBebasPustaka($file_path)
    {
        $file = storage_path('app/public/surat-bebas-pustaka/' . $file_path);

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            abort(404, 'File not found');
        }
    }
}
