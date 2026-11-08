{{-- Report period: shortcuts (Bulan ini, Tahun ini, ...) plus Dari/Sampai, bound to the module's filter.start_date / filter.end_date --}}
@props(['change' => 'fetchData()'])
<label class="report-date report-period">
    <span>Periode</span>
    <select class="filter-panel__input" style="height:28px; font-size:12px;"
        :value="DateUtils.matchPeriod(filter.start_date, filter.end_date)"
        x-on:change="Object.assign(filter, DateUtils.periodRange($event.target.value)); {{ $change }}">
        <option value="" disabled>Kustom</option>
        <option value="this_month">Bulan ini</option>
        <option value="this_year">Tahun ini</option>
        <option value="last_year">Tahun lalu</option>
    </select>
</label>
<label class="report-date">
    <span>Dari</span>
    <input type="date" class="filter-panel__input" x-model="filter.start_date"
        x-on:change="{{ $change }}" style="height:28px; font-size:12px;">
</label>
<label class="report-date">
    <span>Sampai</span>
    <input type="date" class="filter-panel__input" x-model="filter.end_date"
        x-on:change="{{ $change }}" style="height:28px; font-size:12px;">
</label>
