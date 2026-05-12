@props(['label', 'required' => false])
<div>
  <label class="label">
    {{ $label }}@if($required)<span style="color:var(--accent); margin-left:3px;">*</span>@endif
  </label>
  {{ $slot }}
</div>
