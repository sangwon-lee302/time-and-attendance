@props ([
    'method' => 'GET',
    'title' => '',
])

@php
    $method = strtoupper($method);

    $shouldSpoofMethod = in_array($method, ['PUT', 'PATCH', 'DELETE']);

    $realMethod = $shouldSpoofMethod ? 'POST' : $method;
@endphp

<form
    method="{{ $realMethod }}"
    {{ $attributes->merge(['class' => 'max-w-3xl mx-auto flex flex-col gap-12']) }}
    novalidate
>
    @if ($realMethod === 'POST')
        @csrf
    @endif

    @if ($shouldSpoofMethod)
        @method ($method)
    @endif

    @unless (blank($title))
        <h1 class="pb-8 text-center text-4xl">{{ $title }}</h1>
    @endunless

    {{ $slot }}
</form>
