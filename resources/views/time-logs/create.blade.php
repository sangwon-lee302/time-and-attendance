<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main class="flex flex-col items-center pt-20">
        <p class="rounded-full bg-stone-300 px-4 py-2 font-bold text-stone-500">{{ $status }}</p>
        <p id="date" class="pt-8 text-4xl">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
        <p id="time" class="pt-8 pb-20 text-7xl font-bold">{{ now()->format('H:i') }}</p>
        @switch ($status)
            @case ('勤務外')
                <x-form :action="route('time-logs.clock-in')" method="POST">
                    <button class="btn btn-primary rounded-xl px-12">
                        出勤
                    </button>
                </x-form>
                @break
            @case ('出勤中')
                <div class="flex gap-8">
                    <x-form
                        :action="route('time-logs.clock-out')"
                        method="PATCH"
                    >
                        <button class="btn btn-primary rounded-xl px-12">
                            退勤
                        </button>
                    </x-form>
                    <x-form
                        :action="route('time-logs.start-break')"
                        method="POST"
                    >
                        <button class="btn btn-secondary rounded-xl px-12">
                            休憩入
                        </button>
                    </x-form>
                </div>
                @break
            @case ('休憩中')
                <x-form :action="route('time-logs.end-break')" method="PATCH">
                    <button class="btn btn-secondary rounded-xl px-12">
                        休憩戻
                    </button>
                </x-form>
                @break
            @case ('退勤済')
                <p class="text-xl font-bold tracking-widest">お疲れ様でした。</p>
        @endswitch

        @push ('scripts')
            @vite ('resources/js/displayCurrentDatetime.js')
        @endpush
    </x-layouts.main>
</x-layouts.app>
