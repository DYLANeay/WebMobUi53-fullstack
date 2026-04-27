<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-dashboard.js'])
    </x-slot>

    <x-slot:title>
        Sondages
    </x-slot>

    {{-- {{ pour échapper les caractères présents dans les polls (notamment leurs guillemets)}} --}}
    <div id="app"
        data-props="{{ json_encode([
            'polls' => $polls,
            'loginUrl' => route('login'),
        ]) }}"></div>
</x-vue-app-layout>
