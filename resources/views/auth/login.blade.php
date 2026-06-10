<x-layouts.app>
    <x-layouts.header />
    <x-layouts.auth>
        <x-form
            :title="$isAdminLogin ? '管理者ログイン' : 'ログイン'"
            method="POST"
            :action="$isAdminLogin ? route('admin.login.store') : route('login.store')"
        >
            <x-form.input field="email" type="email" />
            <x-form.input field="password" type="password" />
            <button class="btn btn-primary mt-8">
                {{ $isAdminLogin ? '管理者ログインする' : 'ログインする' }}
            </button>
            @unless ($isAdminLogin)
                <a
                    href="{{ route('register') }}"
                    class="mx-auto -mt-6 text-blue-500 hover:underline"
                    >会員登録はこちら</a
                >
            @endunless
        </x-form>
    </x-layouts.auth>
</x-layouts.app>
