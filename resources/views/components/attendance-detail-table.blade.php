@props (['data'])

<table>
    <tbody>
        <tr>
            <th class="text-left">名前</th>
            <td class="text-black">{{ $data['name'] }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th class="text-left">日付</th>
            <td class="text-black">{{ $data['year'] }}</td>
            <td></td>
            <td class="text-black">{{ $data['date'] }}</td>
        </tr>
        <tr>
            <th class="text-left">出勤・退勤</th>
            <td class="text-black">
                <x-conditional-field
                    :input-field-enabled="! $data['isPending']"
                    :place-holder="$data['clockedInAt']"
                    field="clocked_in_at"
                />
            </td>
            <td class="text-black">～</td>
            <td class="text-black">
                <x-conditional-field
                    :input-field-enabled="! $data['isPending']"
                    :place-holder="$data['clockedOutAt']"
                    field="clocked_out_at"
                />
            </td>
        </tr>
        @foreach ($data['breakTimes'] as $breakTime)
            <tr>
                <th class="text-left">
                    休憩{{ ! $loop->first ? $loop->iteration : '' }}
                </th>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $data['isPending']"
                        :place-holder="$breakTime['startedAt']"
                        field="breaks.{{ $loop->index }}.started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $data['isPending']"
                        :place-holder="$breakTime['endedAt']"
                        field="breaks.{{ $loop->index }}.ended_at"
                    />
                </td>
            </tr>
        @endforeach
        {{-- field for a new break time --}}
        @unless ($data['isPending'])
            <tr>
                <th class="text-left">
                    休憩{{ $data['breaksCount'] > 0
                        ? $data['breaksCount'] + 1
                        : '' }}
                </th>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $data['isPending']"
                        field="breaks.{{ $data['breaksCount'] }}.started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-conditional-field
                        :input-field-enabled="! $data['isPending']"
                        field="breaks.{{ $data['breaksCount'] }}.ended_at"
                    />
                </td>
            </tr>
        @endunless
        <tr>
            <th class="text-left">備考</th>
            <td class="text-black" @if (! $data['isPending']) colspan="3"@endif>
                <x-conditional-field
                    :input-field-enabled="! $data['isPending']"
                    :place-holder="$data['remarks']"
                    :text-area-enabled="true"
                    field="remarks"
                    class="text-black"
                />
            </td>
            @if ($data['isPending'])
                <td></td>
                <td></td>
            @endif
        </tr>
    </tbody>
</table>
