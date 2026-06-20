@props (['displayData'])

<table>
    <tbody>
        <tr>
            <th class="text-left">名前</th>
            <td class="text-black">{{ $displayData['name'] }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th class="text-left">日付</th>
            <td class="text-black">{{ $displayData['year'] }}</td>
            <td></td>
            <td class="text-black">{{ $displayData['date'] }}</td>
        </tr>
        <tr>
            <th class="text-left">出勤・退勤</th>
            <td class="text-black">
                <x-conditional-field
                    :input-field-enabled="! $displayData['isPending']"
                    :place-holder="$displayData['clockedInAt']"
                    field="clocked_in_at"
                />
            </td>
            <td class="text-black">～</td>
            <td class="text-black">
                <x-conditional-field
                    :input-field-enabled="! $displayData['isPending']"
                    :place-holder="$displayData['clockedOutAt']"
                    field="clocked_out_at"
                />
            </td>
        </tr>
        @foreach ($displayData['breakTimes'] as $breakTime)
            <tr>
                <th class="text-left">
                    休憩{{ ! $loop->first ? $loop->iteration : '' }}
                </th>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $displayData['isPending']"
                        :place-holder="$breakTime['startedAt']"
                        field="breaks.{{ $loop->index }}.started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $displayData['isPending']"
                        :place-holder="$breakTime['endedAt']"
                        field="breaks.{{ $loop->index }}.ended_at"
                    />
                </td>
            </tr>
        @endforeach
        {{-- field for a new break time --}}
        @unless ($displayData['isPending'])
            <tr>
                <th class="text-left">
                    休憩{{ $displayData['breaksCount'] > 0
                        ? $displayData['breaksCount'] + 1
                        : '' }}
                </th>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $displayData['isPending']"
                        field="breaks.{{ $displayData['breaksCount'] }}.started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $displayData['isPending']"
                        field="breaks.{{ $displayData['breaksCount'] }}.ended_at"
                    />
                </td>
            </tr>
        @endunless
        <tr>
            <th class="text-left">備考</th>
            <td
                class="text-black"
                @if (! $displayData['isPending']) colspan="3"@endif
            >
                <x-conditional-field
                    :input-field-enabled="! $displayData['isPending']"
                    :place-holder="$displayData['remarks']"
                    :text-area-enabled="true"
                    field="remarks"
                    class="text-black"
                />
            </td>
            @if ($displayData['isPending'])
                <td></td>
                <td></td>
            @endif
        </tr>
    </tbody>
</table>
