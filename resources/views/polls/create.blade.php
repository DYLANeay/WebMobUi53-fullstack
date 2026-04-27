<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-edit.js'])
    </x-slot>

    <x-slot:title>
        Nouveau sondage
    </x-slot>

    <div id="app"
        data-props="{{ json_encode([
            'dashboardUrl' => route('polls.dashboard'),
        ]) }}"></div>
</x-vue-app-layout>
