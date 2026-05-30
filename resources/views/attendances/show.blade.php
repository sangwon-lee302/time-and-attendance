<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="mb-8 border-l-6 pl-4 text-2xl">勤怠詳細</h1>
        <x-attendance-detail-table
            :attendance="$attendance"
            :pending-application="$pendingApplication"
        />
        @unless ($pendingApplication)
            @foreach ($attendance->breakTimes as $breakTime)
                <input
                    form="attendance-correction-application"
                    type="hidden"
                    value="{{ $breakTime->id }}"
                    name="breaks[{{ $loop->index }}][break_time_id]"
                />
            @endforeach
        @endunless
        <div>
            @if ($pendingApplication)
                <p class="mt-8 mr-0 ml-auto w-max font-bold text-red-400">*承認待ちのため修正はできません。</p>
            @else
                <form
                    id="attendance-correction-application"
                    action="{{ $isAdmin ? route('admin.attendances.update') : route('attendance-correction-applications.store', $attendance) }}"
                    method="POST"
                    class="mt-8 mr-0 ml-auto w-max"
                >
                    @csrf
                    <button class="btn btn-primary px-8">修正</button>
                </form>
            @endif
        </div>
    </x-layouts.main>
</x-layouts.app>
