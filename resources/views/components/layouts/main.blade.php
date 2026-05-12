<main
    {{ $attributes->merge(['class' => 'mx-auto mt-20 h-full w-[90%] max-w-7xl']) }}
    >{{ $slot }}
</main>
@stack ('scripts')
