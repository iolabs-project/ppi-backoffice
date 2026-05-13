@props(['label', 'required' => false])
<div>
  <label class="label">
    {{ $label }}@if($required)<span class="field__required">*</span>@endif
  </label>
  {{ $slot }}
</div>
