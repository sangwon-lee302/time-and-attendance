@use ('Carbon\Carbon')

@props ([
    'dates' => [],
    'attendances' => [],
])

<table>
    <thead>
        <tr>
            @foreach (['日付', '出勤', '退勤', '休憩', '合計', '詳細'] as $header)
                <th @class (['text-left' => $header === '日付'])
                    >{{ $header }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($dates as $date)
            @php
                $attendance = $attendances[$date] ?? null;
                $date = Carbon::parse($date)->isoFormat('MM/DD(ddd)');
            @endphp
            <tr>
                <td class="text-left">{{ $date }}</td>
                <td>{{ $attendance?->clocked_in_at->format('H:i') ?? '' }}</td>
                <td>
                    {{ $attendance?->clocked_out_at?->format('H:i') ?? '' }}
                </td>
                <td>
                    {{ $attendance?->total_break_time->format('%h:%I') ?? '' }}
                </td>
                <td>
                    {{ $attendance?->total_working_time?->format('%h:%I') ?? '' }}
                </td>
                <td>
                    @if ($attendance)
                        <a
                            href="{{ route('attendances.show', $attendance) }}"
                            class="cursor-pointer text-black hover:underline"
                            >詳細</a
                        >
                    @else
                        <p class="text-black">詳細</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
