<?php

namespace App\Mail;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One email carrying (a part of) a backup archive.
 * For split archives the subject + body tell the admin which part
 * of how many this is and how to reassemble the pieces.
 */
class BackupPartMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Backup $backup,
        public int $part,
        public int $total,
        public string $filePath,
        public string $fileName,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        $subject = $this->total > 1
            ? "پشتیبان‌گیری اینستاشاپ — بخش {$this->part} از {$this->total}"
            : 'فایل پشتیبان اینستاشاپ';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.backup-archive',
            with: [
                'backup' => $this->backup,
                'part' => $this->part,
                'total' => $this->total,
                'fileName' => $this->fileName,
                'createdAt' => $this->backup->completed_at ?? $this->backup->created_at,
            ],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}
