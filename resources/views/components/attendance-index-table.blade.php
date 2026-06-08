@props ([
    'displayData',
    'isAdmin' => false,
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
        @foreach ($displayData['table'] as $row)
            <tr>
                <td class="text-left">
                    {{ $row['date']->isoFormat('MM/DD(ddd)') }}
                </td>
                <td>{{ $row['attendance']?->clocked_in_at->format('H:i') }}</td>
                <td>
                    {{ $row['attendance']?->clocked_out_at?->format('H:i') }}
                </td>
                <td>
                    {{ $row['attendance']?->total_break_time->format('%h:%I') }}
                </td>
                <td>
                    {{ $row['attendance']?->total_working_time?->format('%h:%I') }}
                </td>
                <td>
                    @if ($row['attendance'])
                        <a
                            href="{{ $isAdmin
                                ? route('admin.attendances.show', $row['attendance'])
                                : route('attendances.show', $row['attendance']) }}"
                            class="text-black hover:underline"
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
