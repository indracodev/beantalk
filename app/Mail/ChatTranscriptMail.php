<?php

namespace App\Mail;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ChatTranscriptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $messages;

    public function __construct(
        public Conversation $conversation,
        public string $projectName,
        public string $visitorName,
    ) {
        // Transkrip untuk pelanggan: catatan internal tidak ikut.
        $this->messages = $conversation->messages()->where('is_internal_note', false)->get();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Riwayat Chat Anda — {$this->projectName}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.chat-transcript');
    }

    /**
     * Kirim transkrip ke email pengunjung (atau kontak). false bila tidak ada email / gagal kirim.
     * ponytail: kirim sinkron (shared hosting tanpa worker), pindah ke queue bila SMTP lambat.
     */
    public static function sendTo(Conversation $conversation): bool
    {
        $conversation->loadMissing(['visitor', 'contact', 'project']);
        $visitor = $conversation->visitor;
        $email = $visitor?->email ?: $conversation->contact?->email;

        if (! $email) {
            return false;
        }

        try {
            Mail::to($email)->send(new self(
                $conversation,
                $conversation->project?->name ?? 'BeanTalk',
                $visitor ? ($visitor->name ?: $visitor->display_name) : 'Pengunjung',
            ));

            return true;
        } catch (Throwable $e) {
            Log::warning("[ChatTranscript] Gagal mengirim transkrip: {$e->getMessage()}", ['conversation_id' => $conversation->id]);

            return false;
        }
    }
}
