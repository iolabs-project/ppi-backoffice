{{-- `name` is the request key this field submits (e.g. supplier_id), so a 422 error for it is shown under the field --}}
@props(['label', 'required' => false, 'name' => null])
<div data-field data-field-label="{{ $label }}" @if ($required) data-field-required @endif
  @if ($name) data-field-name="{{ $name }}" @endif>
  <label class="label">
    {{ $label }}@if($required)<span class="field__required">*</span>@endif
  </label>
  {{ $slot }}
</div>
