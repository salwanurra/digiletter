<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Ttd;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Mahasiswa;
use App\Models\SuratTugas;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use App\Events\UserDataInput;
use App\Models\SuratBebasPustaka;
use App\Models\SuratPengajuanCuti;
use App\Models\SuratIzinPenelitian;
use App\Models\SuratKeteranganAktif;
use App\Models\SuratKeteranganAktifOrtuPns;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Services\GoogleDriveService;
use Google_Service_Drive_DriveFile;

class HeadController extends Controller
{
    public function home()
    {
        $totalSuratIzinPenelitian = SuratIzinPenelitian::count();
        $totalSuratKeteranganAktif = SuratKeteranganAktif::count();
        $totalSuratKeteranganAktifOrtuPns = SuratKeteranganAktifOrtuPns::count();
        $totalSuratBebasPustaka = SuratBebasPustaka::count();
        $totalSuratPengajuanCuti = SuratPengajuanCuti::count();
        $totalMahasiswa = Mahasiswa::count();

        $countAllSurat = $totalSuratIzinPenelitian + $totalSuratKeteranganAktif + $totalSuratKeteranganAktifOrtuPns + $totalSuratBebasPustaka + $totalSuratPengajuanCuti;

        $navbarView = view('access/layouts/navbar');
        $sidebarView = view('access/layouts/sidebar');
        return view('access.pages.home', [
            $navbarView,
            $sidebarView,
            'totalSuratIzinPenelitian' => $totalSuratIzinPenelitian,
            'totalSuratKeteranganAktif' => $totalSuratKeteranganAktif,
            'totalSuratKeteranganAktifOrtuPns' => $totalSuratKeteranganAktifOrtuPns,
            'totalSuratBebasPustaka' => $totalSuratBebasPustaka,
            'totalSuratPengajuanCuti' => $totalSuratPengajuanCuti,
            'totalMahasiswa' => $totalMahasiswa,
            'countAllSurat' => $countAllSurat,
        ]);
    }

    public function listdataSuratBebasPustaka()
    {
        $navbarView = view('access/layouts/navbar');
        $sidebarView = view('access/layouts/sidebar');

        $data = SuratBebasPustaka::orderBy('created_at', 'desc')->where('status', 'diproses pimpinan')->get();

        $jenisSurat = 'Surat Bebas Pustaka';

        return view('access.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function setujuiSuratBebasPustaka(Request $request, $id)
    {
        $SuratBebasPustaka = SuratBebasPustaka::findOrFail($id);
        $SuratBebasPustaka->status = 'disetujui';
        $SuratBebasPustaka->updated_at = now();
        $SuratBebasPustaka->save();
        $outputPath = public_path('storage/surat-bebas-pustaka/' . $SuratBebasPustaka->file_path);

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
}
