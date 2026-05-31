<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-8">申請一覧</h1>
        <nav class="mb-8 flex gap-12 border-b px-12 py-4 lg:gap-24 lg:px-24">
            <a
                href="{{ route('attendance-correction-applications.index', ['status' => 'pending']) }}"
                @class (['font-bold' => request()->query('status') != 'approved'])
                >承認待ち</a
            >
            <a
                href="{{ route('attendance-correction-applications.index', ['status' => 'approved']) }}"
                @class (['font-bold' => request()->query('status') === 'approved'])
                >承認済み</a
            >
        </nav>
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        <td>{{ $application->status->label() }}</td>
                        <td>{{ $application->attendance->user->name }}</td>
                        <td>
                            {{ $application->attendance->date->format('Y/m/d') }}
                        </td>
                        <td class="max-w-1/6 truncate">
                            {{ $application->remarks }}
                        </td>
                        <td>{{ $application->created_at->format('Y/m/d') }}</td>
                        <td>
                            <a
                                href="{{ route('attendances.show', $application->attendance->id) }}"
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
