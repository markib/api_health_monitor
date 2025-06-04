<x-mail::message>
    # Endpoint Down: {{ $endpoint->url }}

    **Last Checked:** {{ now()->format('Y-m-d H:i:s') }}

    The endpoint you're monitoring is currently unavailable.

    <x-mail::button :url="url('/api/clients/' . $client->id)">
        View Client
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>