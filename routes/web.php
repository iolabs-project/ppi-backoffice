<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\PenjualanController;
use App\Http\Controllers\Erp\PembelianController;
use App\Http\Controllers\Erp\KasBankController;
use App\Http\Controllers\Erp\LaporanController;
use App\Http\Controllers\Erp\MasterController;

Route::get('/', function () {
    return view('pages.home');
});

Route::prefix('erp')->name('erp.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Penjualan (Sales Orders)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/',              [PenjualanController::class, 'index'])->name('index');
        Route::get('/create',        [PenjualanController::class, 'create'])->name('create');
        Route::get('/{id}',          [PenjualanController::class, 'show'])->name('show');
        Route::get('/{id}/pengiriman', [PenjualanController::class, 'pengiriman'])->name('pengiriman');
    });

    // Pembelian (Purchase Orders)
    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/',              [PembelianController::class, 'index'])->name('index');
        Route::get('/create',        [PembelianController::class, 'create'])->name('create');
        Route::get('/{id}',          [PembelianController::class, 'show'])->name('show');
        Route::get('/{id}/pengiriman', [PembelianController::class, 'pengiriman'])->name('pengiriman');
        Route::get('/{id}/tagihan',  [PembelianController::class, 'tagihan'])->name('tagihan');
    });

    // Kas & Bank
    Route::prefix('kasbank')->name('kasbank.')->group(function () {
        Route::get('/',      [KasBankController::class, 'index'])->name('index');
        Route::get('/{id}',  [KasBankController::class, 'show'])->name('show');
    });

    // Laporan Keuangan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Master Data
    Route::get('/master', [MasterController::class, 'index'])->name('master.index');

    // Coming-soon placeholders
    Route::get('/biaya', function () {
        return view('erp.coming-soon', [
            'currentPage' => 'biaya',
            'breadcrumb'  => [['label' => 'Biaya']],
            'title'       => 'Manajemen Biaya',
            'description' => 'Modul pencatatan dan alokasi biaya operasional sedang dalam pengembangan.',
        ]);
    })->name('biaya.index');

    Route::get('/pengaturan', function () {
        return view('erp.coming-soon', [
            'currentPage' => 'pengaturan',
            'breadcrumb'  => [['label' => 'Pengaturan']],
            'title'       => 'Pengaturan',
            'description' => 'Konfigurasi sistem, pengguna, dan preferensi perusahaan.',
        ]);
    })->name('pengaturan.index');

    Route::get('/bantuan', function () {
        return view('erp.coming-soon', [
            'currentPage' => 'bantuan',
            'breadcrumb'  => [['label' => 'Bantuan']],
            'title'       => 'Pusat Bantuan',
            'description' => 'Dokumentasi dan dukungan pengguna akan tersedia di sini.',
        ]);
    })->name('bantuan.index');
});
