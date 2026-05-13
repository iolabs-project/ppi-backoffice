@props(['breadcrumb' => []])
@php $user = ['name' => 'Albert Irgi', 'role' => 'Superadmin']; @endphp
<header class="topbar">
    <div class="topbar__inner">

        {{-- Breadcrumb --}}
        <div class="topbar__breadcrumb">
            @foreach ($breadcrumb as $i => $crumb)
                @if ($i > 0)
                    <x-misc.icon name="chev-right" :size="14" stroke="var(--ink-5)" />
                @endif
                @if (isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}"
                        class="topbar__crumb {{ $i === count($breadcrumb) - 1 ? 'topbar__crumb--last' : 'topbar__crumb--prev' }}">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span
                        class="topbar__crumb {{ $i === count($breadcrumb) - 1 ? 'topbar__crumb--last' : 'topbar__crumb--prev' }}">
                        {{ $crumb['label'] }}
                    </span>
                @endif
            @endforeach
        </div>

        {{-- Right side --}}
        <div class="topbar__right">
            <div class="topbar__clock" aria-live="polite" aria-label="Current date and time">
                <div class="circle"></div>
                <div class="topbar__clock-date" data-topbar-clock-date></div>
                <div class="topbar__clock-time" data-topbar-clock-time></div>
            </div>


            <div class="topbar__user">
                <x-erp.avatar :name="$user['name']" />
                <div style="line-height:1.1;">
                    <div class="topbar__user-name">{{ $user['name'] }}</div>
                    <div class="topbar__user-role">{{ $user['role'] }}</div>
                </div>
            </div>
        </div>
    </div>
</header>

@once
    @push('scripts')
        <script>
            (() => {
                const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });

                const updateClock = () => {
                    const now = new Date();
                    const dateValue = dateFormatter.format(now);
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    const timeValue = `${hours}:${minutes}:${seconds}`;

                    document.querySelectorAll('[data-topbar-clock-date]').forEach((element) => {
                        element.textContent = dateValue;
                    });

                    document.querySelectorAll('[data-topbar-clock-time]').forEach((element) => {
                        element.textContent = timeValue;
                    });
                };

                updateClock();
                setInterval(updateClock, 1000);
            })
            ();
        </script>
    @endpush
@endonce
