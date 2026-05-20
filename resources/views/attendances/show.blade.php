<x-layouts.app>
    <x-layouts.header />
    <x-layouts.main>
        <h1 class="mb-8 border-l-6 pl-4 text-2xl">勤怠詳細</h1>
        <x-attendance-detail-table :attendance="$attendance" />
        <form
            id="attendance-correction-application"
            action="{{ route('attendance-correction-applications.store') }}"
            class="mt-8 mr-0 ml-auto w-max"
        >
            <button class="btn btn-primary px-8">修正</button>
        </form>
    </x-layouts.main>
</x-layouts.app>
