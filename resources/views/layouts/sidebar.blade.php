@extends('template');

@section('sidebar')
<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="/home" class="app-brand-link">
            <img src="{{ asset('/template/assets/img/branding/e-letter-logo.png') }}" alt="E-Letter" width="180" height="85">
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }} open">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a href="/home" class="menu-link">
                        <div data-i18n="Pembuatan Surat">Pembuatan Surat</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <div data-i18n="Riwayat Surat">Riwayat Surat</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-izin-penelitian') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-izin-penelitian') }}" class="menu-link">
                                <div data-i18n="Surat Izin Penelitian">Surat Izin Penelitian</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-keterangan-aktif') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-keterangan-aktif') }}" class="menu-link">
                                <div data-i18n="Surat Keterangan Aktif">Surat Keterangan Aktif</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-keterangan-aktif-ortu-pns') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-keterangan-aktif-ortu-pns') }}" class="menu-link">
                                <div data-i18n="Surat Keterangan Aktif Ortu Pns">Surat Keterangan Aktif Ortu Pns</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-observasi') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-observasi') }}" class="menu-link">
                                <div data-i18n="Surat Observasi">Surat Observasi</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-bebas-pustaka') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-bebas-pustaka') }}" class="menu-link">
                                <div data-i18n="Surat Bebas Pustaka">Surat Bebas Pustaka</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-pengantar-magang') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-pengantar-magang') }}" class="menu-link">
                                <div data-i18n="Surat Bebas Pustaka">Surat Pengantar Magang</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-rekomendasi-sept') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-rekomendasi-sept') }}" class="menu-link">
                                <div data-i18n="Surat Rekomendasi Mengikuti SEP-T">Surat Rekomendasi Mengikuti SEP-T</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-pengajuan-cuti') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-pengajuan-cuti') }}" class="menu-link">
                                <div data-i18n="Surat Pengajuan Cuti">Surat Pengajuan Cuti</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-pengunduran-diri') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-pengunduran-diri') }}" class="menu-link">
                                <div data-i18n="Surat Pengunduran Diri">Surat Pengunduran Diri</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('riwayat-surat-pindah-kuliah') ? 'active' : '' }}">
                            <a href="{{ route('riwayat-surat-pindah-kuliah') }}" class="menu-link">
                                <div data-i18n="Surat Pindah Kuliah">Surat Pindah Kuliah</div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</aside>
<!-- / Menu -->
@endsection
