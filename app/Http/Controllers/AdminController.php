<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ttd;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Mahasiswa;
use App\Models\SuratTugas;
use App\Models\TtdPimpinan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\UserDataInput;
use App\Models\SuratBebasPustaka;
use App\Models\SuratPengajuanCuti;
use App\Models\SuratIzinPenelitian;
use App\Models\SuratKeteranganAktif;
use App\Models\SuratPengunduranDiri;
use App\Models\SuratPengantarMagang;
use App\Models\SuratRekomendasiSept;
use App\Models\SuratObservasi;
use App\Models\SuratPindahKuliah;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\SuratBebasPustakaExport;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\SuratPengajuanCutiExport;
use App\Exports\SuratIzinPenelitianExport;
use App\Exports\SuratKeteranganAktifExport;
use App\Models\SuratKeteranganAktifOrtuPns;
use App\Exports\SuratKeteranganAktifOrtuPnsExport;
use App\Exports\SuratPengunduranDiriExport;
use App\Exports\SuratObservasiExport;
use App\Exports\SuratPindahKuliahExport;
use App\Exports\SuratRekomendasiSeptExport;
use App\Imports\MahasiswaImport;
use App\Services\GoogleDriveService;
use Google_Service_Drive_DriveFile;

class AdminController extends Controller
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

        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');
        return view('admin.pages.home', [
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


    public function listdataSuratIzinPenelitian()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratIzinPenelitian::orderBy('created_at', 'desc')->get();

        // Menggunakan ucfirst untuk mengubah huruf pertama menjadi besar
        $formattedData = $data->map(function ($item) {
            $item->nama_mhs = ucfirst($item->nama_mhs);
            $item->judul_skripsi = ucfirst($item->judul_skripsi);
            return $item;
        });

        $jenisSurat = 'Surat Izin Penelitian';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratKeteranganAktif()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratKeteranganAktif::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Keterangan Aktif Kuliah';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }
    public function listdataSuratKeteranganAktifOrtuPns()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratKeteranganAktifOrtuPns::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Keterangan Aktif Kuliah Ortu PNS';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }
    public function listdataSuratBebasPustaka()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratBebasPustaka::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Bebas Pustaka';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratPengajuanCuti()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratPengajuanCuti::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Pengajuan Cuti';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratPengunduranDiri()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratPengunduranDiri::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Pengunduran Diri';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratObservasi()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratObservasi::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Observasi';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratPengantarMagang()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratPengantarMagang::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Pengantar Magang';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratPindahKuliah()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratPindahKuliah::orderBy('created_at', 'desc')->get();

        $jenisSurat = 'Surat Pindah Kuliah';

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => $jenisSurat
        ]);
    }

    public function listdataSuratRekomendasiSept()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $data = SuratRekomendasiSept::orderBy('created_at', 'desc')->get();

        return view('admin.pages.listdata', [
            'data' => $data,
            $navbarView,
            $sidebarView,
            'jenisSurat' => 'Surat Rekomendasi Mengikuti SEP-T',
        ]);
    }
    
    public function suratPreview($folder, $file_path)
    {
        // Allowed folders for security
        $allowedFolders = [
            'surat-izin-penelitian',
            'surat-keterangan-aktif',
            'surat-keterangan-aktif-ortu-pns',
            'surat-bebas-pustaka',
            'surat-pengajuan-cuti',
            'surat-pengunduran-diri',
            'surat-observasi',
            'surat-pindah-kuliah',
            'surat-pengantar-magang',
            'surat-rekomendasi-sept',
        ];

        // Ensure the folder is allowed
        if (!in_array($folder, $allowedFolders)) {
            return response()->json(['error' => 'Folder not allowed.'], 400);
        }

        // Initialize GoogleDriveService
        $googleDriveService = new GoogleDriveService();

        try {
            // Parse the file path to get the file name (assuming path is folder/file_name)
            $fileName = basename($file_path);
            $fileId = $this->getFileIdFromPath($folder, $fileName);
            
            if (!$fileId) {
                return response()->json(['error' => "File not found in Google Drive."], 404);
            }

            // Fetch file content
            $fileContent = $googleDriveService->downloadFile($fileId);
            $mimeType = $this->getMimeType($file_path);

            // Create a public URL for the file on Google Drive
            $publicUrl = "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";

            // Handle .docx files with optional conversion
            if ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                $googleDocsUrl = "https://docs.google.com/document/d/{$fileId}/edit";
                return redirect()->away($googleDocsUrl);
            }

            // Return the file as a response
            return redirect()->away($publicUrl);
        } catch (\Exception $e) {
            // Handle any exceptions
            \Log::error("Error fetching file: {$e->getMessage()}");
            return response()->json(['error' => "Error loading file. {$fileContent}"], 500);
        }
    }

    // Helper function to map file extension to mime type
    private function getMimeType($file_path)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
        ];

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Helper function to get file ID from Google Drive based on the folder and file name
    private function getFileIdFromPath($folder, $fileName)
    {
        $googleDriveService = new GoogleDriveService();
        $driveService = $googleDriveService->getService();

        // Folder ID mapping (replace with actual folder IDs in Google Drive)
        $folderIds = [
            'surat-izin-penelitian' => '1w-1ruM9tMAwX6ne9z55e_49jfCEpcIro',
            'surat-keterangan-aktif' => '12uX_fjhrcIS-rqvoycDC2gLUDRioWfPC',
            'surat-keterangan-aktif-ortu-pns' => '1fcxX-JFCfw3YMEHJ8wkMRMQqOulKKkF2',
            'surat-bebas-pustaka' => '1llk8yjHJSE11jWZudisCKwhU1NwvF2Rg',
            'surat-pengajuan-cuti' => '1pDq26rBJEmnMl4tzhDDh-RLunWwYGNFR',
            'surat-pengunduran-diri' => '1BEl0AgclSyBeOVauCRl0Ar_CyTZL9_Ke',
            'surat-observasi' => '1ON-N1NMNBdltBDc3uF2e3CAwTxdkOYAE',
            'surat-pindah-kuliah' => '1exUcEZytAuykkPfkWoyw4HVhQCmcUlvq',
            'surat-pengantar-magang' => '1MJ8oavzSqAJx6ATmRTsm2nWYQeFhNFGO',
            'surat-rekomendasi-sept' => '1_649bqzMYm_6hHa6a6fZQfX5sDBDejJn',
        ];

        $folderId = $folderIds[$folder] ?? null;

        if (!$folderId) {
            return null; // Folder ID not found, return null
        }

        // Search query to find the file by its name within the folder
        $query = "'{$folderId}' in parents and name = '{$fileName}'";

        try {
            // Perform the search query
            $files = $driveService->files->listFiles([
                'q' => $query,
                'spaces' => 'drive',
            ]);

            if (count($files->getFiles()) > 0) {
                // Return the first file's ID (assuming file names are unique in each folder)
                return $files->getFiles()[0]->getId();
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching file ID: {$e->getMessage()}");
        }

        return null; // Return null if file was not found
    }

    public function markAsRead($id)
    {
        if ($id) {
            auth()->user()->notifications->where('id', $id)->markAsRead();
        }

        return redirect()->back();
    }

    public function profile($id)
    {
        abort_unless((int) auth()->id() === (int) $id, 403);

        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');

        $user = auth()->user()->load('mahasiswa');
        $createdAt = $user->created_at;

        return view('admin.pages.profile', compact('navbarView', 'sidebarView', 'user', 'createdAt'));
    }

    public function changePassword(Request $request, $id)
    {
        abort_unless((int) auth()->id() === (int) $id, 403);

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

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

    public function formchangeTtd()
    {
        $navbarView = view('admin/layouts/navbar');
        $sidebarView = view('admin/layouts/sidebar');
        return view('admin.pages.uploadttd', [
            $navbarView,
            $sidebarView
        ]);
    }

    public function changeTtdPimpinan(Request $request)
    {
        $request->validate([
            'penanda_tangan' => 'required|string',
            'ttd_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'nama_pimpinan' => 'required|string',
            'prodi_pimpinan' => 'required|string',
            'nomor_induk' => 'required|string',
        ]);

        $imageName = 'ttd' . '_' . $request->nama_pimpinan . '.' . $request->ttd_image->extension();

        $request->ttd_image->storeAs('public/ttd/terbaru', $imageName);

        // Sesuaikan kondisi where berdasarkan prodi_pimpinan
        $ttd = TtdPimpinan::where('prodi_pimpinan', $request->prodi_pimpinan)->first();

        TtdPimpinan::updateOrCreate(
            ['prodi_pimpinan' => $request->prodi_pimpinan],
            [
                'penanda_tangan' => $request->input('penanda_tangan'),
                'ttd_image' => $imageName,
                'nama_pimpinan' => $request->input('nama_pimpinan'),
                'nomor_induk' => $request->input('nomor_induk'),
            ]
        );

        return redirect()->back()->with('success', 'Tanda Tangan dan Nama Pimpinan pada Surat berhasil diubah.');
    }

    public function exportSuratIzinPenelitian()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Izin Penelitian_' . $tanggal . '.xlsx';

        return Excel::download(new SuratIzinPenelitianExport, $filename);
    }

    public function exportSuratKeteranganAktif()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Keterangan Aktif_' . $tanggal . '.xlsx';

        return Excel::download(new SuratKeteranganAktifExport, $filename);
    }

    public function exportSuratKeteranganAktifOrtuPns()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Keterangan Aktif Ortu Pns_' . $tanggal . '.xlsx';

        return Excel::download(new SuratKeteranganAktifOrtuPnsExport, $filename);
    }

    public function exportSuratBebasPustaka()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Bebas Pustaka_' . $tanggal . '.xlsx';

        return Excel::download(new SuratBebasPustakaExport, $filename);
    }

    public function exportSuratPengajuanCuti()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Pengajuan Cuti_' . $tanggal . '.xlsx';

        return Excel::download(new SuratPengajuanCutiExport, $filename);
    }

    public function exportSuratPengunduranDiri()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Pengajuan Cuti_' . $tanggal . '.xlsx';

        return Excel::download(new SuratPengunduranDiriExport, $filename);
    }

    public function exportSuratObservasi()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Observasi_' . $tanggal . '.xlsx';

        return Excel::download(new SuratObservasiExport, $filename);
    }

    public function exportSuratPindahKuliah()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Pindah Kuliah_' . $tanggal . '.xlsx';

        return Excel::download(new SuratPindahKuliahExport, $filename);
    }

    public function exportSuratPengantarMagang()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Pengantar Magang_' . $tanggal . '.xlsx';

        return Excel::download(new SuratPengantarMagangExport, $filename);
    }

    public function exportSuratRekomendasiSept()
    {
        $tanggal = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $filename = 'Export Surat Rekomendasi Mengikuti SEP-T_' . $tanggal . '.xlsx';

        return Excel::download(new SuratRekomendasiSeptExport, $filename);
    }

    public function indexMahasiswa()
    {
        $navbarView = view('admin.layouts.navbar');
        $sidebarView = view('admin.layouts.sidebar');
        $title = 'Data Mahasiswa';

        // Ambil data mahasiswa dengan relasi dan pengurutan
        $mahasiswa = Mahasiswa::with('user')
            ->orderBy('updated_at', 'desc')
            ->orderByRaw("FIELD(prodi, 'Informatika') DESC")
            ->orderBy('npm', 'desc')
            ->orderBy('prodi')
            ->paginate(10); // Ganti 10 dengan jumlah yang diinginkan

        return view('admin.pages.mahasiswa', [
            'title' => $title,
            'data' => $mahasiswa,
            'navbarView' => $navbarView,
            'sidebarView' => $sidebarView,
        ]);
    }

    public function getAllMahasiswa(Request $request)
    {
        $search = $request->input('search.value');
        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $draw = $request->input('draw');

        $query = Mahasiswa::with('user')
            ->orderBy('updated_at', 'desc')
            ->orderByRaw("FIELD(prodi, 'Informatika') DESC")
            ->orderBy('npm', 'desc')
            ->orderBy('prodi');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('npm', 'like', "%{$search}%")
                    ->orWhere('prodi', 'like', "%{$search}%")
                    ->orWhere('domisili', 'like', "%{$search}%")
                    ->orWhere('jenis_kelamin', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $totalFiltered = $query->count();

        $mahasiswa = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => Mahasiswa::count(),
            'recordsFiltered' => $totalFiltered,
            'data' => $mahasiswa,
        ]);
    }

    public function addMahasiswa(Request $request)
    {
        $existingNpm = Mahasiswa::where('npm', $request->npm)->first();
        if ($existingNpm) {
            return redirect()->route('index.mahasiswa')->with('error', 'NPM sudah ada di database.');
        }

        $mahasiswa = new Mahasiswa();
        $mahasiswa->npm = $request->npm;
        $mahasiswa->prodi = $request->prodi;
        $mahasiswa->status = $request->status;
        $mahasiswa->save();

        return redirect()->route('index.mahasiswa')->with('success', 'Mahasiswa baru berhasil ditambahkan.');
    }

    public function editMahasiswa(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::find($id);

        $mahasiswa->update([
            'npm' => $request->npm,
            'prodi' => $request->prodi,
            'status' => $request->status,
        ]);

        return redirect()->route('index.mahasiswa')->with('success', 'Data mahasiswa berhasil diubah.');
    }

    public function deletelMahasiswa($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();

        return redirect()->route('index.mahasiswa')->with('success', 'Mahasiswa baru berhasil dihapus.');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        try {
            Excel::import(new MahasiswaImport, $request->file('file'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();

            foreach ($errors as &$error) {
                $error = array_map(function ($message) {
                    // remove alert error on row 2
                    return preg_replace('/^There was an error on row \d+\./', '', $message);
                }, $error);
            }

            return redirect()->route('index.mahasiswa')->withErrors($errors)->withInput();
        }
        return redirect()->route('index.mahasiswa')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function viewPembayaran($filename)
    {
        // Instantiate Google Drive Service
        $googleDriveService = new GoogleDriveService();

        // Folder ID for "bukti-pembayaran"
        $folderId = '1p_pb5PQIhosfuTsrhLi1bx2h7pW_fvJu'; // Replace with your actual folder ID

        // Find the file ID using the filename
        $fileId = $googleDriveService->getFileIdByName($filename, $folderId);

        if (!$fileId) {
            abort(404, 'File not found.');
        }

        try {
            // Get the file's metadata to retrieve MIME type
            $fileMetadata = $googleDriveService->getFile($fileId);
            $mimeType = $fileMetadata->getMimeType();

            // Get a temporary public link for the file
            $publicUrl = $googleDriveService->createPublicLink($fileId);

            // Redirect to the file's public URL
            return redirect($publicUrl);
        } catch (\Exception $e) {
            \Log::error("Error retrieving file: " . $e->getMessage());
            abort(500, 'Error retrieving file.');
        }
    }
}
