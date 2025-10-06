@props(['for' => null, 'value' => null])

<label {{ $attributes->merge(['for' => $for, 'class' => 'block text-sm font-medium text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>