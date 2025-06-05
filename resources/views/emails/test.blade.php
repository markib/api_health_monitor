@component('mail::message')
# Hello {{ $recipientName }}

This is a **test email** from your API Health Monitor system.

Everything looks good! 🎉

@component('mail::button', ['url' => config('app.url')])
Visit Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent