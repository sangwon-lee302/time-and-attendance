<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <x-form
            :title="isset($admin) ? '管理者ログイン' : 'ログイン'"
            method="POST"
            :action="isset($admin) ? route('admin.login') : route('login')"
        >
            <x-form.input field="email" type="email" />
            <x-form.input field="password" type="password" />
            <button class="btn btn-primary mt-8">
                {{ isset($admin) ? '管理者ログインする' : 'ログインする' }}
            </button>
            @if (! isset($admin))
                <a
                    href="{{ route('register') }}"
                    class="mx-auto -mt-6 cursor-pointer text-blue-500 hover:underline"
                    >会員登録はこちら</a
                >
            @endif
        </x-form>
    </x-layouts.main>
</x-layouts.app>
