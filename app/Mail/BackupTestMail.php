<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Plain test email to verify the backup SMTP credentials work. */
class BackupTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'ایمیل آزمایشی پشتیبان‌گیری اینستاشاپ');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.backup-test');
    }
}
