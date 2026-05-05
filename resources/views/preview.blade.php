<x-app-layout>
    <x-form action="/" method="POST" title="This is a title."
        ><x-form.input
            field="name"
            type="text"
            placeholder="This is a placeholder."
        />
        <x-form.input
            field="email"
            type="email"
            placeholder="This is an email field."
        />
        <x-form.input
            field="password"
            type="password"
            placeholder="This is a password field."
        />
        <x-form.input
            field="password_confirmation"
            type="password"
            placeholder="This is a password confirmation field."
        />
        <button class="btn btn-primary mt-8">
            This is an inner text of a button.
        </button>
    </x-form>
</x-app-layout>
