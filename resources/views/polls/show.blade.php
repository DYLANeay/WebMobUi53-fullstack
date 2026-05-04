<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-vote.js'])
    </x-slot>

    <x-slot:title>
        {{ $poll->question }}
    </x-slot>

    <div id="app" data-props="{{ json_encode(['poll' => $poll]) }}"></div>
</x-vue-app-layout>
