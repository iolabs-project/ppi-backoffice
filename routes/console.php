<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test {start} {end}', function ($start, $end) {
    $this->comment("Start: $start, End: $end");
    $request = new Request();
    $request->merge(['start_date' => $start, 'end_date' => $end]);
    
    dd(app(App\Services\JournalService::class)->fetchJournalTableData($request, 1)->items());
})->purpose('Test command');
