<x-app-layout class="flex flex-col items-center pt-30 text-xl font-bold">
    <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
    <p>メール認証を完了してください。</p>
    <a
        href="{{ config('mail.mailpit_url') }}"
        class="mt-12 rounded-md border bg-gray-300 px-6 py-2"
        target="_blank"
        rel="noopener noreferrer"
        >認証はこちらから</a
    >
    <x-form :action="route('verification.send')" method="POST">
        <button
            class="mt-12 cursor-pointer text-base font-normal text-blue-500 hover:underline"
        >
            認証メールを再送する
        </button>
    </x-form>
</x-app-layout>
