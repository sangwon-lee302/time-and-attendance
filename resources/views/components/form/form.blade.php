@props ([
    'method' => 'GET',
    'title'  => null,
])

<form
    method="{{ $method }}"
    {{ $attributes->merge(['class' => 'max-w-3xl mx-auto flex flex-col gap-12']) }}
>
    @if ($method == 'POST')
        @csrf
    @endif

    @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
        @method ($method)
    @endif

    @if ($title)
        <h1 class="pb-8 text-center text-4xl font-bold">{{ $title }}</h1>
    @endif

    {{ $slot }}
</form>
