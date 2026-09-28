<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Builds the table rows and totals for printed trade documents
 * (sales/purchase invoices and orders) in the layout of <x-print.document>.
 */
class PrintDocument
{
    /** Item table columns shared by invoices and orders. */
    public static function tradeColumns(): array
    {
        return [
            ['label' => 'Produk'],
            ['label' => 'Deskripsi'],
            ['label' => 'Kuantitas', 'align' => 'right'],
            ['label' => 'Harga', 'align' => 'right'],
            ['label' => 'Diskon', 'align' => 'center'],
            ['label' => 'Pajak', 'align' => 'center'],
            ['label' => 'Jumlah', 'align' => 'right'],
        ];
    }

    /**
     * One row per item: product, code, quantity with unit, unit price, discount %, the document's
     * tax % (tax applies to the whole document) and the line amount after the item discount.
     */
    public static function tradeRows(Collection $items, int|float|string|null $taxPercentage): array
    {
        return $items->map(fn ($item) => [
            $item->product?->name,
            $item->product?->code,
            trim(fmt_doc_qty($item->quantity) . ' ' . ($item->product?->unit?->symbol ?? '')),
            fmt_doc($item->unit_price),
            fmt_doc_percent($item->discount_percentage),
            fmt_doc_percent($taxPercentage),
            fmt_doc($item->total_amount),
        ])->all();
    }

    /**
     * Subtotal (before any discount), total discount, tax, other charges, down payment and total.
     * "Biaya Tambahan" is whatever remains between those lines and the stored total, so the
     * printed lines always add up to the amount on the document.
     */
    public static function tradeTotals(object $doc, Collection $items, ?float $remaining = null): array
    {
        $gross = (float) $items->sum(fn ($item) => $item->quantity * $item->unit_price);
        $discount = (float) $items->sum('discount_amount') + (float) $doc->discount_amount;
        $tax = (float) $doc->tax_amount;
        $downPayment = (float) ($doc->down_payment_amount ?? 0);
        $total = (float) $doc->total_amount;
        $other = round($total - ($gross - $discount + $tax - $downPayment), 2);

        $rows = [['label' => 'Subtotal', 'value' => 'Rp ' . fmt_doc($gross)]];
        if ($discount > 0) {
            $rows[] = ['label' => 'Total Diskon', 'value' => '(Rp ' . fmt_doc($discount) . ')'];
        }
        if ($tax > 0) {
            $rows[] = ['label' => 'Pajak ' . fmt_doc_percent($doc->tax_percentage), 'value' => 'Rp ' . fmt_doc($tax)];
        }
        if (abs($other) >= 0.5) {
            $rows[] = ['label' => 'Biaya Tambahan', 'value' => 'Rp ' . fmt_doc($other)];
        }
        if ($downPayment > 0) {
            $rows[] = ['label' => 'Uang Muka', 'value' => '(Rp ' . fmt_doc($downPayment) . ')'];
        }
        $rows[] = ['label' => 'Total', 'value' => 'Rp ' . fmt_doc($total), 'emphasis' => true];
        if ($remaining !== null) {
            $rows[] = ['label' => 'Sisa Tagihan', 'value' => 'Rp ' . fmt_doc($remaining)];
        }

        return $rows;
    }

    /** Name + address block for a contact (customer / supplier). */
    public static function party(string $heading, ?object $contact): array
    {
        $cityLine = collect([$contact?->city, $contact?->state, $contact?->postal_code])->filter()->implode(', ');

        return [
            'heading' => $heading,
            'name' => $contact?->name ?? '-',
            'lines' => array_values(array_filter([
                $contact?->address,
                $cityLine,
                $contact?->phone ? 'Telp: ' . $contact->phone : null,
                $contact?->email ? 'Email: ' . $contact->email : null,
            ])),
        ];
    }

    /** The company's own block, from config/company.php. */
    public static function company(string $heading = 'Informasi Perusahaan'): array
    {
        $letterhead = config('company.letterhead');

        return [
            'heading' => $heading,
            'name' => $letterhead['name'],
            'lines' => array_values(array_filter([
                $letterhead['address'],
                $letterhead['phone'] ? 'Telp: ' . $letterhead['phone'] : null,
                $letterhead['email'] ? 'Email: ' . $letterhead['email'] : null,
            ])),
        ];
    }
}
