<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ChartController extends Controller
{
    public function index()
    {
        // batas periode yang diinginkan (Agustus 2024 - Juli 2025)
        $start = Carbon::create(2024, 8, 1)->startOfMonth();
        $end   = Carbon::create(2025, 7, 1)->endOfMonth();

        // debug: pastikan range benar
        \Log::info('Range used', ['start' => $start->toDateTimeString(), 'end' => $end->toDateTimeString()]);

        // helper untuk bikin query per tabel
        $getMonthly = function ($table) use ($start, $end) {
            return DB::table($table)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as jumlah")
                ->whereBetween('created_at', [$start, $end]) // FIX: langsung pakai created_at
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('jumlah', 'bulan')
                ->toArray();
        };

        $penelitian     = $getMonthly('surat_izin_penelitian');
        $aktif          = $getMonthly('surat_keterangan_aktif');
        $aktif_pns      = $getMonthly('surat_keterangan_aktif_ortu_pns');
        $observasi      = $getMonthly('surat_observasi');
        $cuti           = $getMonthly('surat_pengajuan_cuti');
        $pindah         = $getMonthly('surat_pindah_kuliah');
        $bebas_pustaka  = $getMonthly('surat_bebas_pustaka');
        $pengunduran    = $getMonthly('surat_pengunduran_diri');

        \Log::info('Penelitian raw', $penelitian);
        \Log::info('Aktif raw', $aktif);

        // generate semua bulan dari $start sampai $end
        $period = CarbonPeriod::create($start, '1 month', $end);
        $months = collect();
        foreach ($period as $dt) {
            $key = $dt->format('Y-m');
            $months->put($key, [
                'label' => $dt->translatedFormat('F Y'),
                'penelitian' => $penelitian[$key] ?? 0,
                'aktif' => $aktif[$key] ?? 0,
                'aktif_pns' => $aktif_pns[$key] ?? 0,
                'pindah' => $pindah[$key] ?? 0,
                'bebas_pustaka' => $bebas_pustaka[$key] ?? 0,
                'observasi' => $observasi[$key] ?? 0,
                'cuti' => $cuti[$key] ?? 0,
                'pengunduran' => $pengunduran[$key] ?? 0,
            ]);
        }

        $months = $months->map(function ($item) {
            $item['total'] = collect($item)->only([
                'penelitian','aktif','aktif_pns','pindah',
                'bebas_pustaka','observasi','cuti','pengunduran'
            ])->sum();
            return $item;
        });

        \Log::info('Months after merge (fixed):', $months->toArray());

        return view('pages.chart', [
            'labels' => $months->pluck('label')->values()->all(),
            'penelitian' => $months->pluck('penelitian')->values()->all(),
            'aktif' => $months->pluck('aktif')->values()->all(),
            'aktif_pns' => $months->pluck('aktif_pns')->values()->all(),
            'pindah' => $months->pluck('pindah')->values()->all(),
            'bebas_pustaka' => $months->pluck('bebas_pustaka')->values()->all(),
            'observasi' => $months->pluck('observasi')->values()->all(),
            'cuti' => $months->pluck('cuti')->values()->all(),
            'pengunduran' => $months->pluck('pengunduran')->values()->all(),
            'total' => $months->pluck('total')->values()->all(),
        ]);
    }
}