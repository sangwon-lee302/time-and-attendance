<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="bd-l-h1 mb-16">スタッフ一覧</h1>
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a
                                href="{{ route('admin.attendances.monthly-index', $user) }}"
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
