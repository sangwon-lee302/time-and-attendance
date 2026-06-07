@props ([
    'attendance',
    'pendingStampCorrection',
    'text' => '',
    'useTextArea' => false,
    'inputType' => 'text',
    'inputName',
    'field' => $inputName,
])

@if ($pendingStampCorrection)
    <p class="whitespace-pre-wrap">{{ $text }}</p>
@else
    <div class="flex flex-col gap-1">
        @if ($useTextArea)
            <textarea
                name="{{ $inputName }}"
                form="stamp-correction"
                rows="5"
                class="rounded-sm border border-neutral-200 px-2 py-1"
                >{{ old($field, $text) }}</textarea
            >
        @else
            <input
                type="{{ $inputType }}"
                name="{{ $inputName }}"
                form="stamp-correction"
                value="{{ old($field, $text) }}"
                class="rounded-sm border border-neutral-200 px-2 py-1 text-center"
            />
        @endif

        @error ($field)
            <p class="err-msg">{{ $message }}</p>
        @enderror
    </div>
@endif
