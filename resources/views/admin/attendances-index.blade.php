<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1">{{ $date->isoFormat('LL') }}の勤怠</h1>
        <div
            class="my-12 flex justify-between rounded-lg bg-white px-4 py-2 font-semibold text-neutral-500"
        >
            <a
                href="{{ route('admin.attendances.index', ['date' => $date->subDay()->format('Y-m-d')]) }}"
                class="flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <p>前日</p>
            </a>
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
                <p class="text-xl font-bold text-black">{{ $date->format('Y/m/d') }}</p>
            </div>
            <a
                href="{{ route('admin.attendances.index', ['date' => $date->addDay()->format('Y-m-d')]) }}"
                class="flex items-center gap-2"
            >
                <p>翌日</p>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    @foreach (['名前', '出勤', '退勤', '休憩', '合計', '詳細'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance->clocked_in_at->format('H:i') }}</td>
                        <td>
                            {{ $attendance->clocked_out_at?->format('H:i') }}
                        </td>
                        <td>
                            {{ $attendance->total_break_time->format('%h:%I') }}
                        </td>
                        <td>
                            {{ $attendance->total_working_time?->format('%h:%I') }}
                        </td>
                        <td>
                            <a
                                href="{{ route('admin.attendances.show', $attendance) }}"
                                class="text-black hover:underline"
                                >詳細</a
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-layouts.main>
</x-layouts.app>
