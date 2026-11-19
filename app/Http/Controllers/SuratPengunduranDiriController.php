<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\User;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\SuratKeteranganAktif;
use App\Models\SuratPengunduranDiri;
use App\Notifications\UserNotifcation;
use App\Notifications\AdminNotification;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\GoogleDriveService;

class SuratPengunduranDiriController extends Controller
{
    public function create($id)
    {
        $userAuth = auth()->user();
        abort_unless($userAuth && $userAuth->mahasiswa && (int) $userAuth->id === (int) $id, 403);
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = $userAuth;

            return view('pages.formsuratpengundurandiri', [$navbarView, $sidebarView, 'data' => $data, 'userAuth']);
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
        
        $data = new SuratPengunduranDiri();
        $data->nama_mhs = Str::title($user->name);
        $data->npm = $user->npm;
        $data->prodi = $user->mahasiswa->prodi;
        $data->alamat = Str::title($request->input('alamat'));
        $data->no_hp = $request->input('no_hp');
        $data->semester = $request->input('semester');

        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_Surat_Pengunduran_Diri_' . Str::title($data->nama_mhs) . '_' . $data->npm . '.docx';
        $filePath =  $fileName;

        $data->file_path = $filePath;
        $data->jenis_surat = 'Surat Pengunduran Diri';

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

        $outputPath = public_path('storage/surat-pengunduran-diri/' . $filePath);

        $this->generateWord($data, $outputPath);

        // Ensure the file exists before uploading
        if (!file_exists($outputPath)) {
            \Log::error("File not found: {$outputPath}");
            return redirect()->back()->with('error', 'Failed to create the PDF file.');
        }

        // Upload to Google Drive
        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1BEl0AgclSyBeOVauCRl0Ar_CyTZL9_Ke'; // Replace with your actual folder ID
            $googleDriveService->uploadFile($outputPath, $filePath, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Pengunduran Diri telah dibuat. Periksa menu Riwayat Surat untuk melihat file surat!');
    }

    private function generateWord($data, $outputPath)
    {
        $templateProcessor = new TemplateProcessor('Surat-Pengunduran-Diri.docx');
        $templateProcessor->setValue('nama_mhs', $data->nama_mhs);
        $templateProcessor->setValue('npm', $data->npm);
        $templateProcessor->setValue('prodi', $data->prodi);
        $templateProcessor->setValue('semester', $data->semester);
        $templateProcessor->setValue('alamat', $data->alamat);
        $templateProcessor->setValue('no_hp', $data->no_hp);
        $templateProcessor->setValue('created_at',  \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D MMMM Y'));

        // jika surat diupdate/disetujui
        $templateProcessor->setValue('nomor_surat', $data->nomor_surat);
        $templateProcessor->setValue('updated_at', \Carbon\Carbon::parse($data->updated_at)->locale('id_ID')->isoFormat('D MMMM Y'));

        $wordDoc = $templateProcessor->saveAs($outputPath);
        return $wordDoc;
    }

    public function riwayatSuratPengunduranDiri()
    {
        $userAuth = auth()->user();
        if ($userAuth && $userAuth->mahasiswa) {
            $navbarView = view('layouts/navbar', compact('userAuth'));
            $sidebarView = view('layouts/sidebar', compact('userAuth'));

            $data = SuratPengunduranDiri::orderBy('created_at', 'desc')->where('nama_mhs', auth()->user()->name)->get();

            $jenisSurat = 'Surat Pengunduran Diri';

            return view('pages.riwayatsurat', [
                'data' => $data,
                $navbarView,
                $sidebarView,
                'jenisSurat' => $jenisSurat
            ]);
        }
    }

    public function setujuiSuratPengunduranDiri(Request $request, $id)
    {
        $SuratPengunduranDiri = SuratPengunduranDiri::findOrFail($id);

        $SuratPengunduranDiri->nomor_surat = $request->input('nomor_surat');
        $SuratPengunduranDiri->status = 'disetujui';
        $SuratPengunduranDiri->updated_at = now();
        $SuratPengunduranDiri->save();

        $outputPath = public_path('storage/surat-pengunduran-diri/' . $SuratPengunduranDiri->file_path);

        // ambil nama_mhs saja dalam 1 data objek
        $nama_mhs = SuratPengunduranDiri::where('nama_mhs', $SuratPengunduranDiri->nama_mhs)
            ->pluck('nama_mhs');

        $users = User::where('role', 'user')
            ->whereIn('name', $nama_mhs)
            ->get();

        foreach ($users as $user) {
            $user->notify(new UserNotifcation([
                'user_id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'jenis_surat' => 'Surat Pengunduran Diri'
            ]));
        }

        $this->generateWord($SuratPengunduranDiri, $outputPath);
        
        try {
            // Folder ID where you want to upload the file (ensure it's shared with the service account)
            $folderId = '1BEl0AgclSyBeOVauCRl0Ar_CyTZL9_Ke'; // Replace with your actual folder ID
            $googleDriveService = new GoogleDriveService();
            $googleDriveService->uploadFile($outputPath, $SuratPengunduranDiri->file_path, $folderId);

            // Delete the temporary local file after uploading
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error uploading file: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to upload file to Google Drive: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Surat Pengunduran Diri telah disetujui!');
    }

    public function tidaksetujuSuratPengunduranDiri(Request $request, $id)
    {
        $SuratPengunduranDiri = SuratPengunduranDiri::findOrFail($id);
        $SuratPengunduranDiri->status = 'ditolak';
        $SuratPengunduranDiri->keterangan = $request->input('text_input');
        $SuratPengunduranDiri->save();

        return redirect()->back()->with('success', 'Surat Pengunduran Diri telah ditolak!');
    }

    public function cancelsuratPengunduranDiri($id)
    {
        $SuratPengunduranDiri = SuratPengunduranDiri::find($id);
        $SuratPengunduranDiri->status = null;
        $SuratPengunduranDiri->keterangan = null;
        $SuratPengunduranDiri->save();
        session()->forget('data_approved_notifications');
        return redirect()->back();
    }


    public function downloadSuratPengunduranDiri($file_path)
    {
        $file = storage_path('app/public/surat-pengunduran-diri/' . $file_path);

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            abort(404, 'File not found');
        }
    }
}
