<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">勤怠詳細</h1>
        <x-attendance-detail-table :data="$data" />
        {{-- hidden fields for break time ids --}}
        @unless ($data['isPending'])
            @foreach ($data['breakTimes'] as $breakTime)
                <input
                    form="attendance-correction"
                    type="hidden"
                    value="{{ $breakTime['id'] }}"
                    name="breaks[{{ $loop->index }}][break_time_id]"
                />
            @endforeach
        @endunless
        @if ($data['isPending'])
            <p
                class="mt-8 mr-0 ml-auto w-max font-bold text-red-400"
            >*承認待ちのため修正はできません。</p>
        @else
            <form
                id="attendance-correction"
                action="{{ auth()->user()?->is_admin
                    ? route('admin.attendances.update', [
                        'attendance' => $data['id'],
                    ])
                    : route('attendance-corrections.store', [
                        'attendance' => $data['id'],
                    ]) }}"
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
