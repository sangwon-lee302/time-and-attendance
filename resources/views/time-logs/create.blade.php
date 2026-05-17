<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main class="flex flex-col items-center pt-20">
        <p class="rounded-full bg-stone-300 px-4 py-2 font-bold text-stone-500">{{ $status }}</p>
        <p id="date" class="pt-8 text-4xl">{{ now()->isoFormat('ll(ddd)') }}</p>
        <p id="time" class="pt-8 pb-20 text-7xl font-bold">{{ now()->format('H:i') }}</p>
        @switch ($status)
            @case ('勤務外')
                <x-form :action="route('time-logs.clock-in')" method="POST">
                    <button class="btn btn-primary rounded-xl px-12">
                        出勤
                    </button>
                </x-form>
                @break
        @endswitch

        @push ('scripts')
            @vite ('resources/js/displayCurrentDatetime.js')
        @endpush
    </x-layouts.main>
</x-layouts.app>
