<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class LaporanController extends Controller
{
    private const REPORTS = [
        'neraca'    => 'Neraca',
        'aruskas'   => 'Arus Kas',
        'labarugi'  => 'Laba Rugi',
        'eksekutif' => 'Eksekutif',
        'utang'     => 'Utang & Piutang',
        'jurnal'    => 'Jurnal Umum',
    ];

    public function index()
    {
        return view('pages.laporan.index', [
            'currentPage' => 'laporan',
            'breadcrumb'  => [['label' => 'Laporan Keuangan']],
            'activeTab'   => null,
        ]);
    }

    public function show(string $report)
    {
        abort_unless(array_key_exists($report, self::REPORTS), 404);

        $data = [
            'currentPage' => 'laporan',
            'breadcrumb'  => [
                ['label' => 'Laporan Keuangan', 'url' => route('laporan.index')],
                ['label' => self::REPORTS[$report]],
            ],
            'activeTab' => $report,
        ];

        // Only compute the data the selected report actually needs, so switching
        // reports doesn't pull in every other report's dataset on every request.
        if (in_array($report, ['neraca', 'labarugi', 'eksekutif'], true)) {
            $chartOfAccounts = ErpDataService::chartOfAccounts();

            $aset       = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '1'));
            $liabilitas = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '2'));
            $ekuitas    = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '3'));
            $pendapatan = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '4'));
            $beban      = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '5') || str_starts_with($a['kode'], '6'));

            $totalAset       = array_sum(array_column(array_values($aset), 'saldo'));
            $totalLiab       = array_sum(array_column(array_values($liabilitas), 'saldo'));
            $totalEkuitas    = array_sum(array_column(array_values($ekuitas), 'saldo'));
            $totalPendapatan = array_sum(array_column(array_values($pendapatan), 'saldo'));
            $totalBeban      = array_sum(array_column(array_values($beban), 'saldo'));
            $labaRugi        = $totalPendapatan - $totalBeban;

            $data += compact(
                'aset',
                'liabilitas',
                'ekuitas',
                'pendapatan',
                'beban',
                'totalAset',
                'totalLiab',
                'totalEkuitas',
                'totalPendapatan',
                'totalBeban',
                'labaRugi'
            );
        }

        if ($report === 'aruskas') {
            $data['months']     = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $data['monthlyIn']  = [820, 950, 1100, 880, 1240, 1050, 960, 1300, 1150, 1080, 1420, 1600];
            $data['monthlyOut'] = [720, 830, 980, 800, 1100, 920, 840, 1180, 1020, 960, 1280, 1450];
        }

        if ($report === 'jurnal') {
            $data['jurnal'] = ErpDataService::jurnal();
        }

        return view('pages.laporan.show', $data);
    }
}
