<header
    class="flex items-center justify-between bg-black px-4 py-2 text-white xl:px-8 xl:py-4"
>
    <img
        src="{{ Vite::asset('resources/images/COACHTECH_logo.png') }}"
        alt="Logo"
        class="max-xl:max-w-1/4"
    />
    @auth
        <nav class="flex gap-4 font-bold xl:gap-8">
            @if ($isAdmin)
                <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
                <x-form :action="route('admin.logout')" method="POST">
                    <button class="cursor-pointer">ログアウト</button>
                </x-form>
            @else
                <a href="{{ route('time-logs.create') }}">勤怠</a>
                <a href="{{ route('attendances.index') }}">勤怠一覧</a>
                <a
                    href="{{ route('attendance-correction-applications.index') }}"
                    >申請</a
                >
                <x-form :action="route('logout')" method="POST">
                    <button class="cursor-pointer">ログアウト</button>
                </x-form>
            @endif
        </nav>
    @endauth
</header>
