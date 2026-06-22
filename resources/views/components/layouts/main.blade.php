<main class="flex-1 bg-neutral-100 pb-20">
    <div
        {{ $attributes->merge(['class' => 'mx-auto mt-20 w-[90%] max-w-7xl relative']) }}
    >
        @session ('custom_message')
            <div class="absolute -top-12 left-1/2 -translate-x-1/2">
                <p
                    class="rounded-full border border-red-400 bg-red-200 px-2 py-1 text-sm text-red-400"
                >{{ $value }}</p>
            </div>
        @endsession
        {{ $slot }}
    </div>
</main>
