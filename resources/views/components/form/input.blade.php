@props (['field'])

<label class="flex flex-col gap-1"
    ><p class="text-xl font-bold">{{ __("validation.attributes.$field") }}</p>
    <input
        name="{{ $field }}"
        {{ $attributes->merge(['class' => 'border rounded-sm p-2']) }}
    />
    @error ($field)
        <p class="text-red-500">{{ $message }}</p>
    @enderror
</label>
