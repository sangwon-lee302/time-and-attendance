<x-app-layout>
    <x-form title="ログイン" method="POST" :action="route('login')">
        <x-form.input field="email" type="email" />
        <x-form.input field="password" type="password" />
        <button class="btn btn-primary mt-8">ログインする</button>
        <a
            href="{{ route('register') }}"
            class="mx-auto -mt-6 cursor-pointer text-blue-500 underline"
            >会員登録はこちら</a
        >
    </x-form>
</x-app-layout>
