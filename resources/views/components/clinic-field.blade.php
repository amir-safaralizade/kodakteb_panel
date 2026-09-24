@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false, 'hint' => null, 'rows' => 3])
<div class="clinic-field {{ $errors->has($name) ? 'has-error' : '' }}">
    <label for="field-{{ $name }}">{{ $label }} @if($required)<span class="required-mark" aria-hidden="true">*</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="field-{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
            aria-describedby="hint-{{ $name }} error-{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select id="field-{{ $name }}" name="{{ $name }}" @required($required)
            aria-describedby="hint-{{ $name }} error-{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ $slot }}</select>
    @else
        <input id="field-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required)
            aria-describedby="hint-{{ $name }} error-{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @endif
    <small class="field-hint" id="hint-{{ $name }}">{{ $hint }}</small>
    <span class="field-error text-danger" id="error-{{ $name }}">@error($name){{ $message }}@enderror</span>
</div>
