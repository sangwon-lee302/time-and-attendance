<main class="flex-1 pb-20">
    <div
        {{ $attributes->merge(['class' => 'mx-auto mt-20 w-[90%] max-w-7xl']) }}
        >{{ $slot }}
    </div>
</main>
