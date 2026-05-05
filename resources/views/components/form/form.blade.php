@props ([
    'method' => 'GET',
    'title'  => null,
])

<form method="{{ $method }}" {{ $attributes }}>
    @if ($method == 'POST')
        @csrf
    @endif

    @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
        @method ($method)
    @endif

    @if ($title)
        <h1 class="pb-20 text-center text-4xl font-bold">{{ $title }}</h1>
    @endif

    {{ $slot }}
</form>
