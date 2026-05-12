@props(['breadcrumb' => []])
@php $user = ['name' => 'Albert Irgi', 'role' => 'Direktur Operasional']; @endphp
<header
    style="position:sticky; top:0; z-index:10; background:rgba(245,242,235,.85); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); border-bottom:1px solid var(--line);">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 28px; gap:24px;">

        {{-- Breadcrumb --}}
        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
            @foreach ($breadcrumb as $i => $crumb)
                @if ($i > 0)
                    <x-erp.icon name="chev-right" :size="14" stroke="var(--ink-5)" />
                @endif
                @if (isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}"
                        style="font-size:13.5px; font-weight:{{ $i === count($breadcrumb) - 1 ? 600 : 500 }}; color:{{ $i === count($breadcrumb) - 1 ? 'var(--ink)' : 'var(--ink-4)' }}; text-decoration:none;">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span
                        style="font-size:13.5px; font-weight:{{ $i === count($breadcrumb) - 1 ? 600 : 500 }}; color:{{ $i === count($breadcrumb) - 1 ? 'var(--ink)' : 'var(--ink-4)' }};">
                        {{ $crumb['label'] }}
                    </span>
                @endif
            @endforeach
        </div>

        {{-- Right side --}}
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="position:relative; width:340px; height:36px;">
                <input placeholder="Cari transaksi, kontak, produk…"
                    style="width:100%; height:36px; padding-left:34px; padding-right:52px; border-radius:10px; background:var(--paper); border:1px solid var(--line); font-size:13px; font-family:inherit; color:var(--ink); outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--ink-4)'" onblur="this.style.borderColor='var(--line)'" />
                <div
                    style="position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none; display:flex;">
                    <x-erp.icon name="search" :size="15" stroke="var(--ink-4)" />
                </div>
                <kbd style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">⌘K</kbd>
            </div>

            <button class="btn btn-ghost btn-icon" style="height:36px; width:36px; border:none;">
                <x-erp.icon name="bell" :size="16" />
            </button>

            <div
                style="display:flex; align-items:center; gap:10px; padding-left:8px; margin-left:4px; border-left:1px solid var(--line);">
                <x-erp.avatar :name="$user['name']" />
                <div style="line-height:1.1;">
                    <div style="font-size:13px; font-weight:600;">{{ $user['name'] }}</div>
                    <div style="font-size:11px; color:var(--ink-4);">{{ $user['role'] }}</div>
                </div>
            </div>
        </div>
    </div>
</header>
