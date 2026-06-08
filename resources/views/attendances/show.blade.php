<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">勤怠詳細</h1>
        <x-stamp-detail-table :display-data="$displayData" />
        {{-- hidden field for break time ids --}}
        @unless ($displayData['isPending'])
            @foreach ($displayData['breakTimes'] as $breakTime)
                <input
                    form="stamp-correction"
                    type="hidden"
                    value="{{ $breakTime['id'] }}"
                    name="breaks[{{ $loop->index }}][break_time_id]"
                />
            @endforeach
        @endunless
        @if ($displayData['isPending'])
            <p
                class="mt-8 mr-0 ml-auto w-max font-bold text-red-400"
            >*承認待ちのため修正はできません。</p>
        @else
            <form
                id="stamp-correction"
                action="{{ auth()->user()?->is_admin
                    ? route('admin.attendances.update', $displayData['id'])
                    : route('stamp-corrections.store', $displayData['id']) }}"
                method="POST"
                class="mt-8 mr-0 ml-auto w-max"
            >
                @csrf
                @if (auth()->user()?->is_admin) @method ('PUT')@endif
                <button class="btn btn-primary px-8">修正</button>
            </form>
        @endif
    </x-layouts.main>
</x-layouts.app>
