<?php
namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Tenant $tenant;

    public function __construct(User $user, Tenant $tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur ComptaSaaS - Votre comptabilité en ligne',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'userName' => $this->user->name,
                'companyName' => $this->tenant->name,
                'loginUrl' => config('app.url') . '/login',
                'supportEmail' => 'support@comptasaas.ci',
            ],
        );
    }
}
