<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">勤怠詳細</h1>
        <x-attendance-detail-table
            :attendance="$attendance"
            :pending-stamp-correction="$pendingStampCorrection"
        />
        @unless ($pendingStampCorrection)
            @foreach ($attendance->breakTimes as $breakTime)
                <input
                    form="stamp-correction"
                    type="hidden"
                    value="{{ $breakTime->id }}"
                    name="breaks[{{ $loop->index }}][break_time_id]"
                />
            @endforeach
        @endunless
        @if ($pendingStampCorrection)
            <p class="mt-8 mr-0 ml-auto w-max font-bold text-red-400">*承認待ちのため修正はできません。</p>
        @else
            <form
                id="stamp-correction"
                action="{{ $isAdmin
                    ? route('admin.attendances.update', $attendance)
                    : route('stamp-corrections.store', $attendance) }}"
                method="POST"
                class="mt-8 mr-0 ml-auto w-max"
            >
                @csrf
                @if ($isAdmin) @method ('PATCH')@endif
                <button class="btn btn-primary px-8">修正</button>
            </form>
        @endif
    </x-layouts.main>
</x-layouts.app>
