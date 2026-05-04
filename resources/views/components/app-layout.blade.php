<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>勤怠管理システム</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header
        class="flex items-center justify-between bg-black px-4 py-2 text-white lg:px-8 lg:py-4"
    >
        <img
            src="{{ Vite::asset('resources/images/COACHTECH_logo.png') }}"
            alt="Logo"
        />
        @auth
            <nav class="flex gap-4 lg:gap-8">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button>ログアウト</button>
                </form>
            </nav>
        @endauth
    </header>
    <main
        {{ $attributes->merge(['class' => 'mx-auto mt-20 h-full w-[90%] max-w-7xl']) }}
        >{{ $slot }}
    </main>
    @stack ('scripts')
</body>
</html>
