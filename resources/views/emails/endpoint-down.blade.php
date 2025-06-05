<x-mail::message>
    # Endpoint Monitoring Alert

    **Endpoint:** {{ $endpoint->url }}
    **Status:** DOWN
    **Last Checked:** {{ now()->format('Y-m-d H:i:s') }}

    <x-mail::button :url="url('/api/clients/' . $client->id)">
        View Client
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>