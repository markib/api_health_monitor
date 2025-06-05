<?php

namespace App\Mail;

use App\Models\Endpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EndpointDownNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The endpoint instance.
     *
     * @var \App\Models\Endpoint
     */
    public Endpoint $endpoint;

    /**
     * Create a new message instance.
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'alerts@example.com',
            subject: "{$this->endpoint->url} is unavailable!"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.endpoint-down',
            with: ['endpoint' => $this->endpoint,
            'client' => $this->endpoint->client]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('alerts@example.com')
            ->subject("{$this->endpoint} is unavailable!")
            ->view('emails.endpoint_down')
            ->with([
                'endpoint' => $this->endpoint,
            ]);
    }
}
