<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Endpoint;

class EndpointDownNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The URL of the endpoint that is down.
     *
     * @var string
     */
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(public Endpoint $endpoint)
    {
         $this->url = $endpoint->url;
    }
    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Endpoint Down Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
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
            ->subject("{$this->url} is unavailable!")
            ->view('emails.endpoint_down')
            ->with([
                'endpoint' => $this->endpoint,
            ]);
    }
}
