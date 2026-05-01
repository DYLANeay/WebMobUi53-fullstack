<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-edit.js'])
    </x-slot>

    <x-slot:title>
        Modifier le sondage
    </x-slot>

    <div id="app"
        data-props="{{ json_encode([
            'poll' => $poll->load('options'),
            'dashboardUrl' => $dashboardUrl,
        ]) }}"></div>
</x-vue-app-layout>
