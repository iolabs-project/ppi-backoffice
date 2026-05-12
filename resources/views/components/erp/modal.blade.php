@props(['title', 'show', 'width' => 560, 'closeHandler' => 'modal = null'])
<div x-show="{{ $show }}" x-cloak
     style="position:fixed; inset:0; z-index:50; display:grid; place-items:center; background:rgba(20,15,8,.35); backdrop-filter:blur(2px);"
     x-on:click="{{ $closeHandler ?? 'modal=null' }}">
  <div x-on:click.stop style="width:{{ $width }}px; max-width:92vw; max-height:88vh; overflow:auto; background:var(--paper); border-radius:var(--r-card); box-shadow:var(--shadow-pop); border:1px solid var(--line);">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 22px; border-bottom:1px solid var(--line);">
      <h3 class="display" style="margin:0; font-size:16px; font-weight:700;">{{ $title }}</h3>
      <button class="btn btn-ghost btn-icon btn-sm" x-on:click="{{ $closeHandler ?? 'modal=null' }}" style="border:none;">
        <x-erp.icon name="x" :size="16" />
      </button>
    </div>
    <div style="padding:20px 22px;">{{ $slot }}</div>
    @isset($footer)
      <div style="padding:14px 22px; border-top:1px solid var(--line); display:flex; justify-content:flex-end; gap:10px; background:var(--bg);">
        {{ $footer }}
      </div>
    @endisset
  </div>
</div>
