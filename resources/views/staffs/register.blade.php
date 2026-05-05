<x-app-layout>
    <x-form title="会員登録" method="POST" action="{{ route('register') }}">
        <x-form.input field="name" type="text" />
        <x-form.input field="email" type="email" />
        <x-form.input field="password" type="password" />
        <x-form.input field="password_confirmation" type="password" />
        <button class="btn btn-primary mt-8">登録する</button>
    </x-form>
</x-app-layout>
