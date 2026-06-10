@if ($inputFieldEnabled)
    <div class="flex flex-col gap-1">
        @if ($textAreaEnabled)
            <textarea
                name="{{ $inputName }}"
                form="stamp-correction"
                rows="5"
                class="rounded-sm border border-neutral-200 px-2 py-1"
                >{{ old($field, $placeHolder) }}</textarea
            >
        @else
            <input
                type="{{ $inputType }}"
                name="{{ $inputName }}"
                form="stamp-correction"
                value="{{ old($field, $placeHolder) }}"
                class="rounded-sm border border-neutral-200 px-2 py-1 text-center"
            />
        @endif

        @error ($field)
            <p class="err-msg">{{ $message }}</p>
        @enderror
    </div>
@else
    <p class="whitespace-pre-wrap">{{ $placeHolder }}</p>
@endif
