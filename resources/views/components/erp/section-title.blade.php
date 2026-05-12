@props(['title', 'subtitle' => null])
<div style="display:flex; justify-content:space-between; align-items:flex-end; gap:12px; margin-bottom:14px;">
  <div>
    <h2 class="display" style="margin:0; font-size:18px; font-weight:700; letter-spacing:-0.01em;">{{ $title }}</h2>
    @if($subtitle)
      <div style="font-size:12.5px; color:var(--ink-4); margin-top:2px;">{{ $subtitle }}</div>
    @endif
  </div>
  @if(isset($action))
    <div>{{ $action }}</div>
  @endif
</div>
