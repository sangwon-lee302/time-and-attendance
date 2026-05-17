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
            <x-form action="{{ route('logout') }}" method="POST">
                <button class="cursor-pointer text-xl font-medium">
                    ログアウト
                </button>
            </x-form>
        </nav>
    @endauth
</header>
