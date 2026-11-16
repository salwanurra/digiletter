<?php

namespace App\Http\Controllers;

use App\Models\SuratPindahKuliah;
use Illuminate\Http\Request;
use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratPindahKuliahController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = User::where('id', $id)->first();

            return view('pages.formsuratpindahkuliah', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
        }
    }

    public function store(Request $request)
    {
        
        $data = new SuratPindahKuliah();
        $data->nama_mhs = Str::title($request->input('nama_mhs'));
        $data->npm = $request->input('npm');
        $data->prodi = $request->input('prodi');
        $data->no_hp = $request->input('no_hp');
        $data->semester = $request->input('semester');
        $data->nama_ortu = $request->input('nama_ortu');
        $data->prodi_tujuan = $request->input('prodi_tujuan');
        $data->fakultas_tujuan = $request->input('fakultas_tujuan');
        $data->univ_tujuan = $request->input('univ_tujuan');

        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Pindah_Kuliah_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.docx';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Pindah Kuliah';

        $data->save();

        $googleDriveService = new GoogleDriveService();

        // Notify admins
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => $data->jenis_surat
            ]));
        }

        $outputPath = public_path('storage/surat-pindah-kuliah/' . $filePath);

        $this->generateWord($data, $outputPath);

        // Ensure the file exists before uploading
        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        // Upload to Google Drive
        try {
            $folderId = '1exUcEZytAuykkPfkWoyw4HVhQCmcUlvq';
            $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Pindah Kuliah telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generateWord($data, $outputPath)
    {
        $templateProcessor = new TemplateProcessor('Surat-Pindah-Kuliah.docx');
        $templateProcessor->setValue('nama_mhs', $data->nama_mhs);
        $templateProcessor->setValue('npm', $data->npm);
        $templateProcessor->setValue('prodi', $data->prodi);
        $templateProcessor->setValue('semester', $data->semester);
        $templateProcessor->setValue('no_hp', $data->no_hp);
        $templateProcessor->setValue('nama_ortu', $data->nama_ortu);
        $templateProcessor->setValue('prodi_tujuan', $data->prodi_tujuan);
        $templateProcessor->setValue('fakultas_tujuan', $data->fakultas_tujuan);
        $templateProcessor->setValue('univ_tujuan', $data->univ_tujuan);
        $templateProcessor->setValue('created_at',  \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D MMMM Y'));

        // jika surat diupdate/disetujui
        $templateProcessor->setValue('nomor_surat', $data->nomor_surat);
        $templateProcessor->setValue('updated_at', \Carbon\Carbon::parse($data->updated_at)->locale('id_ID')->isoFormat('D MMMM Y'));

        $wordDoc = $templateProcessor->saveAs($outputPath);
        return $wordDoc;
    }

    public function riwayatSuratPindahKuliah()
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratPindahKuliah::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Pindah Kuliah';

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat
            ]);
        }
    }

    public function setujuiSuratPindahKuliah(Request $request, $id)
    {
        $SuratPindahKuliah = SuratPindahKuliah::findOrFail($id);

        $SuratPindahKuliah->nomor_surat = $request->input('nomor_surat');
        $SuratPindahKuliah->status = 'disetujui';
        $SuratPindahKuliah->updated_at = now();
        $SuratPindahKuliah->save();

        $outputPath = public_path('storage/surat-pindah-kuliah/' . $SuratPindahKuliah->file_path);

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratPindahKuliah::where('nama_mhs', $SuratPindahKuliah->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Pindah Kuliah'
            ]));
        }

        return redirect()->back()->with('success', 'Surat Pindah Kuliah telah disetujui!');
    }

    public function tidaksetujuSuratPindahKuliah(Request $request, $id)
    {
        $SuratPindahKuliah = SuratPindahKuliah::findOrFail($id);
        $SuratPindahKuliah->status = 'ditolak';
        $SuratPindahKuliah->keterangan = $request->input('text_input');
        $SuratPindahKuliah->save();

        return redirect()->back()->with('success', 'Surat Pindah Kuliah telah ditolak!');
    }

    public function cancelsuratPindahKuliah($id)
    {
        $SuratPindahKuliah = SuratPindahKuliah::find($id);
        $SuratPindahKuliah->status = null;
        $SuratPindahKuliah->keterangan = null;
        $SuratPindahKuliah->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }


    public function downloadSuratPindahKuliah($file_path)
    {
        $file = storage_path('app/public/surat-pindah-kuliah/' . $file_path);

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            abort(404, 'File not found');
        }
    }
}
