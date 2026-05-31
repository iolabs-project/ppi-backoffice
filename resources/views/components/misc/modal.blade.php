@props(['title', 'show', 'width' => 560, 'closeHandler' => 'modal = null'])
<div x-show="{{ $show }}" x-cloak
     class="modal-overlay"
     x-on:click="{{ $closeHandler ?? 'modal=null' }}">
  <div x-on:click.stop class="modal-box" style="width:{{ $width }}px;">
    <div class="modal-hd">
      <h3 class="modal-title display">{{ $title }}</h3>
      <div style="display:flex; align-items:center; gap:6px;">
        @isset($headerActions){{ $headerActions }}@endisset
        <button class="btn btn-ghost btn-icon btn-sm" x-on:click="{{ $closeHandler ?? 'modal=null' }}" style="border:none;">
          <x-misc.icon name="x" :size="16" />
        </button>
      </div>
    </div>
    <div class="modal-bd">{{ $slot }}</div>
    @isset($footer)
      <div class="modal-ft">
        {{ $footer }}
      </div>
    @endisset
  </div>
</div>
