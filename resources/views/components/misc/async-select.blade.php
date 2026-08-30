@props([
    'url',
    'display',
    'default' => '[]',
    'params' => '{}',
    'searchParam' => 'search',
    'debounce' => 300,
    'hasValue' => 'false',
    'placeholder' => 'Cari...',
    'minWidth' => null,
    'align' => 'left',
    'height' => '40px',
    'triggerClass' => '',
    'triggerStyle' => '',
])
{{--
    Async version of x-misc.select: fetches its option list from `url` (expects a JSON
    response shaped either `{ data: [...] }` or a Laravel paginator, both of which expose
    the items under `.data`) instead of requiring the caller to preload everything.

    - Fetches on first open, and again (debounced) whenever the search text changes.
    - `default` seeds `items` before the first fetch resolves, so an already-selected
      value (e.g. when editing an existing record) has something to render immediately —
      pass e.g. default="formData.warehouse ? [formData.warehouse] : []".
    - `params` is re-evaluated on every fetch, so it can reference reactive outer state
      (e.g. params="{ warehouse_id: warehouse.id }").
    - The item list itself is rendered by the caller via the default slot, exactly like
      x-misc.select: `<template x-for="o in items" :key="o.id">...</template>`, with
      `items` and `loading` exposed on this component's scope.
--}}
<div class="dropdown-wrap" x-data="{
        open: false,
        q: '',
        loading: false,
        fetched: false,
        items: {{ $default }},
        _searchTimer: null,
        async fetchOptions() {
            this.loading = true;
            try {
                const r = await axios.get('{{ $url }}', {
                    params: Object.assign({ '{{ $searchParam }}': this.q }, ({{ $params }}))
                });
                this.items = r.data?.data ?? r.data ?? [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
                this.fetched = true;
            }
        },
        onSearchInput() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => this.fetchOptions(), {{ $debounce }});
        },
    }"
    x-on:close-dropdowns.window="open = false; q = ''"
    x-on:click.outside="open = false; q = ''"
    x-effect="if (open) { $nextTick(() => $refs.searchInput && $refs.searchInput.focus()) }">
    <div class="input dropdown-trigger {{ $triggerClass }}" style="height:{{ $height }}; {{ $triggerStyle }}" x-on:click="
        let wasOpen = open;
        $dispatch('close-dropdowns');
        if (!wasOpen) {
            let r = $el.getBoundingClientRect();
            $refs.menu.style.top = (r.bottom + 4) + 'px';
            @if ($align === 'right')
                $refs.menu.style.right = (window.innerWidth - r.right) + 'px';
                $refs.menu.style.left = 'auto';
            @else
                $refs.menu.style.left = r.left + 'px';
                $refs.menu.style.right = 'auto';
            @endif
            $refs.menu.style.width = r.width + 'px';
            open = true;
            if (!fetched) fetchOptions();
        } else {
            open = false;
        }
    ">
        @isset($trigger)
            {{ $trigger }}
        @else
            <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                :style="({{ $hasValue }}) ? '' : 'color:var(--ink-4);'" x-text="{{ $display }}"></span>
        @endisset
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--ink-4)" stroke-width="1.6"
            stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </div>
    <div x-ref="menu" class="dropdown-menu" x-show="open" x-cloak
        @if ($minWidth) style="min-width:{{ $minWidth }};" @endif>
        <div class="dropdown-search">
            <input type="text" class="dropdown-search__input" x-ref="searchInput" x-model="q"
                x-on:input="onSearchInput()" x-on:click.stop placeholder="{{ $placeholder }}" />
        </div>
        <div class="dropdown-list">
            <template x-if="loading">
                <div class="dropdown-empty">Memuat...</div>
            </template>
            <template x-if="!loading">
                {{ $slot }}
            </template>
            <template x-if="!loading && items.length === 0">
                <div class="dropdown-empty">Tidak ditemukan</div>
            </template>
        </div>
    </div>
</div>
