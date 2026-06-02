@php
    $authRoutes = ['login', 'register', 'verification.notice'];
    $isAuthPage = in_array(Route::currentRouteName(), $authRoutes);
@endphp

<main @class (['flex-1 pb-20', 'bg-neutral-100' => ! $isAuthPage])>
    <div
        {{ $attributes->merge(['class' => 'mx-auto mt-20 w-[90%] max-w-7xl']) }}
        >{{ $slot }}
    </div>
</main>
