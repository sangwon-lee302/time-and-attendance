<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">勤怠詳細</h1>
        <x-attendance-detail-table :data="$data" />
        <form
            action="{{ route('admin.attendance-corrections.approve', [
                'attendance_correction' => $data['id'],
            ]) }}"
            method="POST"
            class="mr-0 ml-auto w-max"
        >
            @csrf
            @method ('PUT')
            <button
                @class ([
                    'btn btn-primary mt-12 px-8',
                    'cursor-default' => $data['isApproved'],
                ])
                @disabled ($data['isApproved'])
            >
                {{ $data['isApproved'] ? '承認済み' : '承認' }}
            </button>
        </form>
    </x-layouts.main>
</x-layouts.app>
