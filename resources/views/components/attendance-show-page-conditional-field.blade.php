@props ([
    'attendance' => null,
    'pendingApplication' => null,
    'text' => '',
    'useTextArea' => false,
    'inputType' => 'text',
    'inputName' => '',
    'field' => $inputName,
])

@if ($pendingApplication)
    <p class="whitespace-pre-wrap">{{ $text }}</p>
@else
    <div class="flex flex-col gap-1">
        @if ($useTextArea)
            <textarea
                name="{{ $inputName }}"
                form="attendance-correction-application"
                rows="5"
                class="rounded-sm border border-neutral-200 px-2 py-1"
                >{{ old($field, $text) }}</textarea
            >
        @else
            <input
                type="{{ $inputType }}"
                name="{{ $inputName }}"
                form="attendance-correction-application"
                value="{{ old($field, $text) }}"
                class="rounded-sm border border-neutral-200 px-2 py-1 text-center"
            />
        @endif

        @error ($field)
            <p class="text-red-500">{{ $message }}</p>
        @enderror
    </div>
@endif
