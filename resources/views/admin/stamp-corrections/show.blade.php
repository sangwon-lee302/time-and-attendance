<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">勤怠詳細</h1>
        <x-stamp-detail-table :display-data="$displayData" />
        {{-- hidden field for break time ids --}}
        @unless ($displayData['isPending'])
            @foreach ($displayData['breakTimes'] as $breakTime)
                <input
                    form="stamp-correction"
                    type="hidden"
                    value="{{ $breakTime['id'] }}"
                    name="breaks[{{ $loop->index }}][id]"
                />
            @endforeach
        @endunless
        <form
            action="{{ route('admin.stamp-corrections.approve', $displayData['id']) }}"
            method="POST"
            class="mr-0 ml-auto w-max"
        >
            @csrf
            @method ('PUT')
            <button
                @class ([
                    'btn btn-primary mt-12 px-8',
                    'cursor-default' => $displayData['isApproved'],
                ])
                @disabled ($displayData['isApproved'])
            >
                {{ $displayData['isApproved'] ? '承認済み' : '承認' }}
            </button>
        </form>
    </x-layouts.main>
</x-layouts.app>
