{{--
    Printable document in the layout of the company's invoice. Used by every print view:
    title + meta (Nomor, Tanggal, ...), two parties, an item table, totals, amount in words, signatures.
--}}
@props([
    'title',
    'meta' => [],               // ['Nomor' => 'INV/01948', 'Tanggal' => '21/09/2026', ...]
    'parties' => [],            // [['heading' => ..., 'name' => ..., 'lines' => [...]], ...]
    'columns' => [],            // [['label' => 'Produk', 'align' => 'left|center|right'], ...]
    'rows' => [],               // [['Katul', '', '33.330', ...], ...]
    'totals' => [],             // [['label' => 'Total', 'value' => 'Rp 1.000', 'emphasis' => true], ...]
    'amountInWords' => null,    // number → "Terbilang ... Rupiah"
    'note' => null,
    'signatures' => [],         // [['caption' => 'Dengan Hormat,', 'name' => ..., 'role' => 'Jabatan'], ...]
])
<div class="doc">
    <header class="doc-head">
        <div class="doc-company">{{ config('company.letterhead.name') }}</div>
        <div class="doc-titleblock">
            <div class="doc-title">{{ $title }}</div>
            <table class="doc-meta">
                @foreach ($meta as $label => $value)
                    <tr>
                        <td class="doc-meta__label">{{ $label }}</td>
                        <td class="doc-meta__value">{{ $value ?: '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </header>

    <section class="doc-parties">
        @foreach ($parties as $party)
            <div class="doc-party">
                <div class="doc-party__heading">{{ $party['heading'] }}</div>
                <div class="doc-party__name">{{ $party['name'] }}</div>
                @foreach ($party['lines'] as $line)
                    <div class="doc-party__line">{{ $line }}</div>
                @endforeach
            </div>
        @endforeach
    </section>

    <table class="doc-items">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th class="is-{{ $column['align'] ?? 'left' }}">{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $i => $cell)
                        <td class="is-{{ $columns[$i]['align'] ?? 'left' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="is-left" colspan="{{ count($columns) }}">Tidak ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($totals)
        <table class="doc-totals">
            @foreach ($totals as $total)
                <tr @class(['is-emphasis' => $total['emphasis'] ?? false])>
                    <td class="doc-totals__label">{{ $total['label'] }}</td>
                    <td class="doc-totals__value">{{ $total['value'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($amountInWords !== null)
        <div class="doc-words">
            <div>Terbilang</div>
            <div>{{ terbilang($amountInWords) }} Rupiah</div>
        </div>
    @endif

    @if ($note)
        <div class="doc-note">
            <div class="doc-note__label">Catatan</div>
            <div>{{ $note }}</div>
        </div>
    @endif

    @if ($signatures)
        <div class="doc-signatures">
            @foreach ($signatures as $signature)
                <div class="doc-signature">
                    <div>{{ $signature['caption'] }}</div>
                    <div class="doc-signature__space"></div>
                    <div @class(['doc-signature__name', 'is-blank' => empty($signature['name'])])>{{ $signature['name'] ?: '(................................)' }}</div>
                    @if (!empty($signature['role']))
                        <div>{{ $signature['role'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@once
    @push('styles')
        <style>
            .doc-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 24px;
                margin-bottom: 26px;
            }

            .doc-company,
            .doc-title {
                color: var(--doc-blue);
                font-weight: 700;
                line-height: 1.15;
            }

            .doc-company { font-size: 26px; }
            .doc-title { font-size: 28px; text-align: right; margin-bottom: 10px; }

            .doc-meta { margin-left: auto; border-collapse: collapse; font-size: 12px; }
            .doc-meta td { padding: 0 0 1px; }
            .doc-meta__label { text-align: right; padding-right: 40px !important; }
            .doc-meta__value { text-align: right; }

            .doc-parties {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0 64px;
                margin-bottom: 14px;
            }

            .doc-party__heading {
                font-weight: 700;
                font-size: 12px;
                padding-bottom: 8px;
                margin-bottom: 14px;
                border-bottom: 2px solid var(--doc-navy);
            }

            .doc-party__name { color: var(--doc-blue); font-weight: 700; font-size: 13px; margin-bottom: 8px; }
            .doc-party__line { color: var(--doc-muted); }

            .doc-items {
                width: 100%;
                border-collapse: separate;
                border-spacing: 2px;
                margin: 0 -2px 14px;
            }

            .doc-items th {
                background: var(--doc-navy);
                color: #fff;
                font-weight: 700;
                padding: 10px 8px;
            }

            .doc-items td {
                background: var(--doc-cell);
                color: var(--doc-muted);
                padding: 10px 8px;
            }

            .doc-items .is-left { text-align: left; }
            .doc-items .is-center { text-align: center; }
            .doc-items .is-right { text-align: right; }

            .doc-totals {
                margin-left: auto;
                border-collapse: collapse;
                font-weight: 700;
                font-size: 13px;
            }

            .doc-totals td { padding: 6px 0; }
            .doc-totals__label { text-align: right; padding-right: 64px !important; }
            .doc-totals__value { text-align: right; min-width: 140px; }

            .doc-totals .is-emphasis td {
                font-size: 17px;
                text-decoration: underline;
            }

            .doc-words { margin-top: 10px; }
            .doc-note { margin-top: 12px; color: var(--doc-muted); }
            .doc-note__label { color: var(--doc-ink); font-weight: 700; }

            .doc-signatures {
                display: flex;
                justify-content: flex-end;
                gap: 64px;
                margin-top: 28px;
                break-inside: avoid;
            }

            .doc-signature { text-align: center; min-width: 170px; }
            .doc-signature__space { height: 120px; }
            .doc-signature__name { text-decoration: underline; }
            .doc-signature__name.is-blank { text-decoration: none; } /* the dotted line is the signing space */

            @media screen and (max-width: 820px) {
                .doc-head { flex-direction: column; gap: 12px; }
                .doc-titleblock { width: 100%; }
                .doc-parties { grid-template-columns: 1fr; gap: 18px; }
                .doc-items { display: block; overflow-x: auto; }
                .doc-totals__label { padding-right: 24px !important; }
            }

            @media print {
                .doc-items thead { display: table-header-group; } /* repeat the header on every page */
                .doc-items tr { break-inside: avoid; }
            }
        </style>
    @endpush
@endonce
