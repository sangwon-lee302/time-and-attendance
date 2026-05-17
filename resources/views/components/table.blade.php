@props ([
    'headers' => [],
    'contents' => [],
])

<table class="rounded-lg font-bold text-neutral-500">
    <thead>
        <tr>
            @foreach ($headers as $header)
                <th class="border-b-4 border-neutral-200">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($contents as $row)
            <tr>
                @foreach ($row as $cell)
                    <td class="border-neutral-200 not-last:border-b-2">
                        {{ $cell ?? '' }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
