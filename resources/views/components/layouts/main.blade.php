@php
    $authRoutes = ['login', 'register', 'verification.notice'];
    $isAuthPage = in_array(Route::currentRouteName(), $authRoutes);
@endphp

<main @class (['flex-1 pb-20', 'bg-neutral-100' => ! $isAuthPage])>
    <div
        {{ $attributes->merge(['class' => 'mx-auto mt-20 w-[90%] max-w-7xl']) }}
    >
        @error ('custom_error')
            <span class="bg-red-200 text-sm text-red-500">{{ $message }}</span>
        @enderror
        {{ $slot }}
    </div>
</main>
