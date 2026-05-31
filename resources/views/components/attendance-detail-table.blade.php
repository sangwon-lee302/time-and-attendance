@props (['attendance'])

<table>
    <tbody>
        <tr>
            <th class="text-left">名前</th>
            <td class="text-black">{{ $attendance->user->name }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th class="text-left">日付</th>
            <td class="text-black">{{ $attendance->date->format('Y年') }}</td>
            <td></td>
            <td class="text-black">
                {{ $attendance->date->format('n月j日') }}
            </td>
        </tr>
        <tr>
            <th class="text-left">出勤・退勤</th>
            <td class="text-black">
                <x-attendance-show-page-conditional-field
                    :attendance="$attendance"
                    :pending-application="$pendingApplication"
                    :text="$attendance->clocked_in_at->format('H:i')"
                    input-name="new_clocked_in_at"
                />
            </td>
            <td class="text-black">～</td>
            <td class="text-black">
                <x-attendance-show-page-conditional-field
                    :attendance="$attendance"
                    :pending-application="$pendingApplication"
                    :text="$attendance->clocked_out_at?->format('H:i')"
                    input-name="new_clocked_out_at"
                />
            </td>
        </tr>
        @foreach ($attendance->breakTimes as $breakTime)
            <tr>
                <th class="text-left">
                    休憩{{ ! $loop->first ? $loop->iteration : '' }}
                </th>
                <td class="text-black">
                    <x-attendance-show-page-conditional-field
                        :attendance="$attendance"
                        :pending-application="$pendingApplication"
                        :text="$breakTime->started_at->format('H:i')"
                        input-name="breaks[{{ $loop->index }}][new_started_at]"
                        field="breaks.{{ $loop->index }}.new_started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-attendance-show-page-conditional-field
                        :attendance="$attendance"
                        :pending-application="$pendingApplication"
                        :text="$breakTime->ended_at->format('H:i')"
                        input-name="breaks[{{ $loop->index }}][new_ended_at]"
                        field="breaks.{{ $loop->index }}.new_ended_at"
                    />
                </td>
            </tr>
        @endforeach
        @unless ($pendingApplication)
            <tr>
                <th class="text-left">
                    休憩{{ $attendance->breakTimes ? count($attendance->breakTimes) + 1 : '' }}
                </th>
                <td class="text-black">
                    <x-attendance-show-page-conditional-field
                        :attendance="$attendance"
                        :pending-application="$pendingApplication"
                        input-name="breaks[{{ count($attendance->breakTimes) }}][new_started_at]"
                        field="breaks.{{ count($attendance->breakTimes) }}.new_started_at"
                    />
                </td>
                <td class="text-black">～</td>
                <td class="text-black">
                    <x-attendance-show-page-conditional-field
                        :attendance="$attendance"
                        :pending-application="$pendingApplication"
                        input-name="breaks[{{ count($attendance->breakTimes) }}][new_ended_at]"
                        field="breaks.{{ count($attendance->breakTimes) }}.new_ended_at"
                    />
                </td>
            </tr>
        @endunless
        <tr>
            <th class="text-left">備考</th>
            <td
                class="text-black"
                @unless ($pendingApplication) colspan="3"@endunless
            >
                <x-attendance-show-page-conditional-field
                    :attendance="$attendance"
                    :pending-application="$pendingApplication"
                    :text="$pendingApplication?->remarks"
                    :use-text-area="true"
                    input-name="remarks"
                    class="text-black"
                />
            </td>
        </tr>
    </tbody>
</table>
