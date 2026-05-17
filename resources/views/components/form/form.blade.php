@props ([
    'method' => 'GET',
    'title'  => null,
])

@php
    $spoofMethod = in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']) ? $method : null;
    $method = $spoofMethod ? 'POST' : $method;
@endphp

<form
    method="{{ $method }}"
    {{ $attributes->merge(['class' => 'max-w-3xl mx-auto flex flex-col gap-12']) }}
>
    @if ($method == 'POST')
        @csrf
    @endif

    @if ($spoofMethod)
        @method ($spoofMethod)
    @endif

    @if ($title)
        <h1 class="pb-8 text-center text-4xl font-bold">{{ $title }}</h1>
    @endif

    {{ $slot }}
</form>
