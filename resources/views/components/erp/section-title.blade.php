@props(['title', 'subtitle' => null])
<div class="section-title">
  <div class="section-title__text">
    <h2 class="section-title__heading display">{{ $title }}</h2>
    @if($subtitle)
      <div class="section-title__sub">{{ $subtitle }}</div>
    @endif
  </div>
  @if(isset($action))
    <div>{{ $action }}</div>
  @endif
</div>
