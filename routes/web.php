<?php

use App\Http\Controllers\AdminCampusIdentityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CampusIdentityController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ForgotPWController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistController;
use App\Http\Controllers\SuratBebasPustakaController;
use App\Http\Controllers\SuratIzinPenelitianController;
use App\Http\Controllers\SuratKeteranganAktifController;
use App\Http\Controllers\SuratKeteranganAktifOrtuPnsController;
use App\Http\Controllers\SuratObservasiController;
use App\Http\Controllers\SuratPengajuanCutiController;
use App\Http\Controllers\SuratPengantarMagangController;
use App\Http\Controllers\SuratPengunduranDiriController;
use App\Http\Controllers\SuratPindahKuliahController;
use App\Http\Controllers\SuratRekomendasiSeptController;
use App\Http\Controllers\UserController;
use App\Services\VerificationEmailCooldown;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ======== auth ============
Route::get('/', function () {
    return redirect()->away('https://panduan.marooners.my.id');
});
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('postlogin', [LoginController::class, 'postlogin'])->name('postlogin');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegistController::class, 'index'])->name('regist');
Route::post('/register', [RegistController::class, 'create'])->name('post_regist');
Route::post('/cek-npm', [RegistController::class, 'cekNPM'])->name('cek-npm');
Route::get('/email/verify', function (Request $request, VerificationEmailCooldown $cooldown) {
    $user = $request->user();
    $deliveryWarning = null;
    $initialEmailSent = false;

    if (! $user->hasVerifiedEmail()
        && is_null($user->verification_email_sent_at)
        && ! $request->session()->has('warning')
        && $cooldown->acquire($user)) {
        try {
            $user->sendEmailVerificationNotification();
            $initialEmailSent = true;
        } catch (\Throwable $exception) {
            $cooldown->clear($user);
            report($exception);
            $deliveryWarning = 'Email verifikasi belum dapat dikirim. Silakan coba lagi.';
        }
    }

    return view('auth.verify-email', [
        'retryAfter' => $cooldown->remaining($user),
        'deliveryWarning' => $deliveryWarning,
        'initialEmailSent' => $initialEmailSent,
    ]);
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request, VerificationEmailCooldown $cooldown) {
    $currentToken = (string) $request->user()->email_verification_token;
    $requestToken = (string) $request->query('token');

    abort_unless(
        $currentToken !== '' && $requestToken !== '' && hash_equals($currentToken, $requestToken),
        403,
        'Tautan verifikasi ini sudah tidak berlaku. Gunakan email verifikasi terbaru.'
    );

    $request->fulfill();
    $cooldown->clear($request->user());

    return redirect()->route('home')->with('success', 'Email berhasil diverifikasi.');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request, VerificationEmailCooldown $cooldown) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('home');
    }

    if (! $cooldown->acquire($request->user())) {
        $seconds = $cooldown->remaining($request->user());

        return back()->with('warning', "Tunggu {$seconds} detik sebelum mengirim ulang email verifikasi.");
    }

    try {
        $request->user()->sendEmailVerificationNotification();
    } catch (\Throwable $exception) {
        $cooldown->clear($request->user());
        report($exception);

        return back()->with('warning', 'Email verifikasi belum dapat dikirim. Silakan coba lagi.');
    }

    return back()->with('status', 'verification-link-sent');
})->middleware('auth')->name('verification.send');
Route::get('forgotpw', [ForgotPWController::class, 'index'])->name('forgot-pw');
Route::post('forgotpw', [ForgotPWController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ForgotPWController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ForgotPWController::class, 'reset'])->name('password.update');
Route::get('/chart', [ChartController::class, 'index'])->name('chart');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/verifikasi-identitas-kampus', [CampusIdentityController::class, 'show'])
        ->name('campus-identity.show');
    Route::post('/verifikasi-identitas-kampus', [CampusIdentityController::class, 'submit'])
        ->middleware('throttle:5,1')
        ->name('campus-identity.submit');
});

// ======== user =============
Route::middleware(['auth', 'verified', 'campus.verified'])->group(function () {
    Route::get('/home', [UserController::class, 'home'])->name('home');

    // pembuatan surat
    Route::get('/suratizinpenelitian/create/{id}', [SuratIzinPenelitianController::class, 'create'])->name('suratizinpenelitian.create');
    Route::post('/suratizinpenelitian', [SuratIzinPenelitianController::class, 'store'])->name('suratizinpenelitian.store');
    Route::get('/suratketeranganaktif/create/{id}', [SuratKeteranganAktifController::class, 'create'])->name('suratketeranganaktif.create');
    Route::post('/suratketeranganaktif', [SuratKeteranganAktifController::class, 'store'])->name('suratketeranganaktif.store');
    Route::get('/suratketeranganaktifortupns/create/{id}', [SuratKeteranganAktifOrtuPnsController::class, 'create'])->name('suratketeranganaktifortupns.create');
    Route::post('/suratketeranganaktifortupns', [SuratKeteranganAktifOrtuPnsController::class, 'store'])->name('suratketeranganaktifortupns.store');
    Route::get('/suratbebaspustaka/create/{id}', [SuratBebasPustakaController::class, 'create'])->name('suratbebaspustaka.create');
    Route::post('/suratbebaspustaka', [SuratBebasPustakaController::class, 'store'])->name('suratbebaspustaka.store');
    Route::get('/suratpengajuancuti/create/{id}', [SuratPengajuanCutiController::class, 'create'])->name('suratpengajuancuti.create');
    Route::post('/suratpengajuancuti', [SuratPengajuanCutiController::class, 'store'])->name('suratpengajuancuti.store');
    Route::get('/suratpengundurandiri/create/{id}', [SuratPengunduranDiriController::class, 'create'])->name('suratpengundurandiri.create');
    Route::post('/suratpengundurandiri', [SuratPengunduranDiriController::class, 'store'])->name('suratpengundurandiri.store');
    Route::get('/suratobservasi/create/{id}', [SuratObservasiController::class, 'create'])->name('suratobservasi.create');
    Route::post('/suratobservasi', [SuratObservasiController::class, 'store'])->name('suratobservasi.store');
    Route::get('/suratpindahkuliah/create/{id}', [SuratPindahKuliahController::class, 'create'])->name('suratpindahkuliah.create');
    Route::post('/suratpindahkuliah', [SuratPindahKuliahController::class, 'store'])->name('suratpindahkuliah.store');
    Route::get('/suratpengantarmagang/create/{id}', [SuratPengantarMagangController::class, 'create'])->name('suratpengantarmagang.create');
    Route::post('/suratpengantarmagang', [SuratPengantarMagangController::class, 'store'])->name('suratpengantarmagang.store');
    Route::get('/suratrekomendasisept/create/{id}', [SuratRekomendasiSeptController::class, 'create'])->name('suratrekomendasisept.create');
    Route::post('/suratrekomendasisept', [SuratRekomendasiSeptController::class, 'store'])->name('suratrekomendasisept.store');

    // riwayat surat
    Route::get('/riwayat-surat-izin-penelitian', [SuratIzinPenelitianController::class, 'riwayatSuratIzinPenelitian'])->name('riwayat-surat-izin-penelitian');
    Route::get('/riwayat-surat-keterangan-aktif', [SuratKeteranganAktifController::class, 'riwayatSuratKeteranganAktif'])->name('riwayat-surat-keterangan-aktif');
    Route::get('/riwayat-surat-keterangan-aktif-ortu-pns', [SuratKeteranganAktifOrtuPnsController::class, 'riwayatSuratKeteranganAktifOrtuPns'])->name('riwayat-surat-keterangan-aktif-ortu-pns');
    Route::get('/riwayat-surat-bebas-pustaka', [SuratBebasPustakaController::class, 'riwayatSuratBebasPustaka'])->name('riwayat-surat-bebas-pustaka');
    Route::get('/riwayat-surat-pengajuan-cuti', [SuratPengajuanCutiController::class, 'riwayatSuratPengajuanCuti'])->name('riwayat-surat-pengajuan-cuti');
    Route::get('/riwayat-surat-pengunduran-diri', [SuratPengunduranDiriController::class, 'riwayatSuratPengunduranDiri'])->name('riwayat-surat-pengunduran-diri');
    Route::get('/riwayat-observasi', [SuratObservasiController::class, 'riwayatSuratObservasi'])->name('riwayat-surat-observasi');
    Route::get('/riwayat-surat-pindah-kuliah', [SuratPindahKuliahController::class, 'riwayatSuratPindahKuliah'])->name('riwayat-surat-pindah-kuliah');
    Route::get('/riwayat-surat-pengantar-magang', [SuratPengantarMagangController::class, 'riwayatSuratPengantarMagang'])->name('riwayat-surat-pengantar-magang');
    Route::get('/riwayat-surat-rekomendasi-sept', [SuratRekomendasiSeptController::class, 'riwayatSuratRekomendasiSept'])->name('riwayat-surat-rekomendasi-sept');

    // download surat
    Route::get('/download-surat/{folders}/{file_path}', [UserController::class, 'downloadSurat'])->name('download-surat');

    Route::get('/markasreadapprove/{id}', [UserController::class, 'markAsReadApprove'])->name('markasreadapprove');
    Route::get('/user/profile/{id}', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile/{id}', [UserController::class, 'lengkapiProfile'])->name('user.lengkapiprofile');
    Route::get('/user/settings-account/{id}', [UserController::class, 'settingAccount'])->name('user.settingAccount');
    Route::get('/user/settings-security/{id}', [UserController::class, 'settingSecurity'])->name('user.settingSecurity');
    Route::post('/user/settings/{id}', [UserController::class, 'changePassword'])->name('change.password');
    Route::get('/user/user-guide/{id}', [UserController::class, 'userGuide'])->name('user.userGuide');
});

// ======== admin ============
Route::middleware(['admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'home'])->name('home.admin');
    Route::get('/admin/verifikasi-identitas', [AdminCampusIdentityController::class, 'index'])
        ->name('admin.campus-identities.index');
    Route::post('/admin/verifikasi-identitas/{verification}/setujui', [AdminCampusIdentityController::class, 'approve'])
        ->name('admin.campus-identities.approve');
    Route::post('/admin/verifikasi-identitas/{verification}/tolak', [AdminCampusIdentityController::class, 'reject'])
        ->name('admin.campus-identities.reject');
    Route::get('/storage/{folders}/{file_path}', [AdminController::class, 'suratPreview'])
        ->name('surat-preview');

    // listdata
    Route::get('/admin/listdata/suratizinpenelitian', [AdminController::class, 'listdataSuratIzinPenelitian'])->name('listdata.suratizinpenelitian');
    Route::get('/admin/listdata/suratketeranganaktif', [AdminController::class, 'listdataSuratKeteranganAktif'])->name('listdata.suratketeranganaktif');
    Route::get('/admin/listdata/suratketeranganaktifortupns', [AdminController::class, 'listdataSuratKeteranganAktifOrtuPns'])->name('listdata.suratketeranganaktifortupns');
    Route::get('/admin/listdata/suratbebaspustaka', [AdminController::class, 'listdataSuratBebasPustaka'])->name('listdata.suratbebaspustaka');
    Route::get('/admin/listdata/suratpengajuancuti', [AdminController::class, 'listdataSuratPengajuanCuti'])->name('listdata.suratpengajuancuti');
    Route::get('/admin/listdata/suratpengundurandiri', [AdminController::class, 'listdataSuratPengunduranDiri'])->name('listdata.suratpengundurandiri');
    Route::get('/admin/listdata/suratobservasi', [AdminController::class, 'listdataSuratObservasi'])->name('listdata.suratobservasi');
    Route::get('/admin/listdata/suratpindahkuliah', [AdminController::class, 'listdataSuratPindahKuliah'])->name('listdata.suratpindahkuliah');
    Route::get('/admin/listdata/suratpengantarmagang', [AdminController::class, 'listdataSuratPengantarMagang'])->name('listdata.suratpengantarmagang');
    Route::get('/admin/listdata/suratrekomendasisept', [AdminController::class, 'listdataSuratRekomendasiSept'])->name('listdata.suratrekomendasisept');

    Route::get('/markasread/{id}', [AdminController::class, 'markAsRead'])->name('markasread');

    // approve reject cancel
    Route::post('/setujui-surat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'setujuiSuratIzinPenelitian']);
    Route::post('/tidaksetuju-surat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'tidaksetujuSuratIzinPenelitian']);
    Route::delete('/cancelsurat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'cancelsuratIzinPenelitian']);

    Route::post('/setujui-surat-keterangan-aktif/{id}', [SuratKeteranganAktifController::class, 'setujuiSuratKeteranganAktif']);
    Route::post('/tidaksetuju-surat-keterangan-aktif/{id}', [SuratKeteranganAktifController::class, 'tidaksetujuSuratKeteranganAktif']);
    Route::delete('/cancelsurat-keterangan-aktif/{id}', [SuratKeteranganAktifController::class, 'cancelsuratKeteranganAktif']);

    Route::post('/setujui-surat-keterangan-aktif-ortu-pns/{id}', [SuratKeteranganAktifOrtuPnsController::class, 'setujuiSuratKeteranganAktifOrtuPns']);
    Route::post('/tidaksetuju-surat-keterangan-aktif-ortu-pns/{id}', [SuratKeteranganAktifOrtuPnsController::class, 'tidaksetujuSuratKeteranganAktifOrtuPns']);
    Route::delete('/cancelsurat-keterangan-aktif-ortu-pns/{id}', [SuratKeteranganAktifOrtuPnsController::class, 'cancelsuratKeteranganAktifOrtuPns']);

    Route::post('/setujui-surat-bebas-pustaka/{id}', [SuratBebasPustakaController::class, 'setujuiSuratBebasPustaka']);
    Route::post('/tidaksetuju-surat-bebas-pustaka/{id}', [SuratBebasPustakaController::class, 'tidaksetujuSuratBebasPustaka']);
    Route::delete('/cancelsurat-bebas-pustaka/{id}', [SuratBebasPustakaController::class, 'cancelsuratBebasPustaka']);

    Route::post('/setujui-surat-pengajuan-cuti/{id}', [SuratPengajuanCutiController::class, 'setujuiSuratPengajuanCuti']);
    Route::post('/tidaksetuju-surat-pengajuan-cuti/{id}', [SuratPengajuanCutiController::class, 'tidaksetujuSuratPengajuanCuti']);
    Route::delete('/cancelsurat-pengajuan-cuti/{id}', [SuratPengajuanCutiController::class, 'cancelsuratPengajuanCuti']);

    Route::post('/setujui-surat-pengunduran-diri/{id}', [SuratPengunduranDiriController::class, 'setujuiSuratPengunduranDiri']);
    Route::post('/tidaksetuju-surat-pengunduran-diri/{id}', [SuratPengunduranDiriController::class, 'tidaksetujuSuratPengunduranDiri']);
    Route::delete('/cancelsurat-pengunduran-diri/{id}', [SuratPengunduranDiriController::class, 'cancelsuratPengunduranDiri']);

    Route::post('/setujui-surat-observasi/{id}', [SuratObservasiController::class, 'setujuiSuratObservasi']);
    Route::post('/tidaksetuju-surat-observasi/{id}', [SuratObservasiController::class, 'tidaksetujuSuratObservasi']);
    Route::delete('/cancelsurat-observasi/{id}', [SuratObservasiController::class, 'cancelsuratObservasi']);

    Route::post('/setujui-surat-pindah-kuliah/{id}', [SuratPindahKuliahController::class, 'setujuiSuratPindahKuliah']);
    Route::post('/tidaksetuju-surat-pindah-kuliah/{id}', [SuratPindahKuliahController::class, 'tidaksetujuSuratPindahKuliah']);
    Route::delete('/cancelsurat-pindah-kuliah/{id}', [SuratPindahKuliahController::class, 'cancelsuratPindahKuliah']);

    Route::post('/setujui-surat-pengantar-magang/{id}', [SuratPengantarMagangController::class, 'setujuiSuratPengantarMagang']);
    Route::post('/tidaksetuju-surat-pengantar-magang/{id}', [SuratPengantarMagangController::class, 'tidaksetujuSuratPengantarMagang']);
    Route::delete('/cancelsurat-pengantar-magang/{id}', [SuratPengantarMagangController::class, 'cancelsuratPengantarMagang']);

    Route::post('/setujui-surat-rekomendasi-sept/{id}', [SuratRekomendasiSeptController::class, 'setujuiSuratRekomendasiSept']);
    Route::post('/tidaksetuju-surat-rekomendasi-sept/{id}', [SuratRekomendasiSeptController::class, 'tidaksetujuSuratRekomendasiSept']);
    Route::delete('/cancelsurat-rekomendasi-sept/{id}', [SuratRekomendasiSeptController::class, 'cancelsuratRekomendasiSept']);

    // Bukti telah membayar UKT
    Route::get('/admin/bukti-pembayaran/{filename}', [AdminController::class, 'viewPembayaran'])->name('admin.pembayaran');

    // profil admin
    Route::get('/admin/profile/{id}', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/admin/profile/{id}', [AdminController::class, 'changePassword'])->name('change.password.admin');

    // change ttd
    Route::get('/admin/upload-ttd/', [AdminController::class, 'formchangeTtd'])->name('form.change.ttd');
    Route::post('/admin/upload-ttd/', [AdminController::class, 'changeTtdPimpinan'])->name('change.ttdpimpinan');

    // data mhs
    Route::get('/admin/index-mahasiswa/', [AdminController::class, 'indexMahasiswa'])->name('index.mahasiswa');
    Route::get('/admin/data-mahasiswa/', [AdminController::class, 'getAllMahasiswa'])->name('data.mahasiswa');
    Route::post('/admin/data-mahasiswa/', [AdminController::class, 'addMahasiswa'])->name('add.mahasiswa');
    Route::patch('/admin/data-mahasiswa/{id}', [AdminController::class, 'editMahasiswa'])->name('edit.mahasiswa');
    Route::delete('/admin/data-mahasiswa/{id}', [AdminController::class, 'deletelMahasiswa'])->name('delete.mahasiswa');

    // export excel
    Route::get('/export-surat-izin-penelitian', [AdminController::class, 'exportSuratIzinPenelitian'])->name('export-surat-izin-penelitian');
    Route::get('/export-surat-keterangan-aktif', [AdminController::class, 'exportSuratKeteranganAktif'])->name('export-surat-keterangan-aktif');
    Route::get('/export-surat-keterangan-aktif-ortu-pns', [AdminController::class, 'exportSuratKeteranganAktifOrtuPns'])->name('export-surat-keterangan-aktif-ortu-pns');
    Route::get('/export-surat-bebas-pustaka', [AdminController::class, 'exportSuratBebasPustaka'])->name('export-surat-bebas-pustaka');
    Route::get('/export-surat-pengajuan-cuti', [AdminController::class, 'exportSuratPengajuanCuti'])->name('export-surat-pengajuan-cuti');
    Route::get('/export-surat-pengunduran-diri', [AdminController::class, 'exportSuratPengunduranDiri'])->name('export-surat-pengunduran-diri');
    Route::get('/export-surat-observasi', [AdminController::class, 'exportSuratObservasi'])->name('export-surat-observasi');
    Route::get('/export-surat-pindah-kuliah', [AdminController::class, 'exportSuratPindahKuliah'])->name('export-surat-pindah-kuliah');
    Route::get('/export-surat-pengantar-magang', [AdminController::class, 'exportSuratPengantarMagang'])->name('export-surat-pengantar-magang');
    Route::get('/export-surat-rekomendasi-sept', [AdminController::class, 'exportSuratRekomendasiSept'])->name('export-surat-rekomendasi-sept');

    // import excel
    Route::post('/admin/data-mahasiswa/import', [AdminController::class, 'importExcel'])->name('import.mahasiswa');

});
Route::get('/clear-cache', function () {
    if (request()->query('token') !== env('MAINT_TOKEN')) {
        abort(403, 'Forbidden');
    }
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');

    return 'Cache cleared';
});
