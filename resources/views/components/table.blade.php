@use ('Carbon\Carbon')

@props ([
    'dates' => [],
    'attendances' => [],
])

<table
    class="w-full table-fixed rounded-lg bg-white font-bold text-neutral-500"
>
    <tr>
        @foreach (['日付', '出勤', '退勤', '休憩', '合計', '詳細'] as $header)
            <th
                @class ([
                            'border-b-4 border-neutral-200 py-4',
                            'text-left'     => $header === '日付',
                            'pl-4 lg:pl-12' => $loop->first,
                            'pr-4 lg:pr-12' => $loop->last,
                        ])
                >{{ $header }}
            </th>
        @endforeach
    </tr>
    @foreach ($dates as $date)
        @php
            $attendance = $attendances[$date] ?? null;
            $date = Carbon::parse($date)->isoFormat('MM/DD(ddd)');
        @endphp
        <tr>
            <td
                @class ([
                            'border-neutral-200 py-4 text-left pl-4 lg:pl-12',
                            'border-b-2' => ! $loop->last
                        ])
            >
                {{ $date }}
            </td>
            <td
                @class ([
                            'border-neutral-200 py-4 text-center',
                            'border-b-2' => ! $loop->last
                        ])
            >
                {{ $attendance?->clocked_in_at->format('H:i') ?? '' }}
            </td>
            <td
                @class ([
                            'border-neutral-200 py-4 text-center',
                            'border-b-2' => ! $loop->last
                        ])
            >
                {{ $attendance?->clocked_out_at->format('H:i') ?? '' }}
            </td>
            <td
                @class ([
                            'border-neutral-200 py-4 text-center',
                            'border-b-2' => ! $loop->last
                        ])
            >
                {{ $attendance?->total_break_time->format('%h:%I') ?? '' }}
            </td>
            <td
                @class ([
                            'border-neutral-200 py-4 text-center',
                            'border-b-2' => ! $loop->last
                        ])
            >
                {{ $attendance?->total_working_time->format('%h:%I') ?? '' }}
            </td>
            <td
                @class ([
                            'border-neutral-200 py-4 text-center pr-4 lg:pr-12',
                            'border-b-2' => ! $loop->last
                        ])
            >
                @if ($attendance)
                    <a
                        href="{{ route('attendances.show', $attendance) }}"
                        class="cursor-pointer text-black hover:underline"
                        >詳細</a
                    >
                @endif
            </td>
        </tr>
    @endforeach
</table>
