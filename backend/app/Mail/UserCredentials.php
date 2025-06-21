<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $isNewUser;
    public $changedFields;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password = null, bool $isNewUser = true, array $changedFields = [])
    {
        $this->user = $user;
        $this->password = $password;
        $this->isNewUser = $isNewUser;
        $this->changedFields = $changedFields;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isNewUser 
            ? 'Welcome to Animal Science Days - Your Account Details' 
            : 'Your Account Details Have Been Updated - Animal Science Days';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.user-credentials',
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
}