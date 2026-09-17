<?php

namespace App\Mail;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChatTranscriptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $conversation;
    public $messages;
    public $projectName;
    public $visitorName;

    public function __construct(Conversation $conversation, string $projectName, string $visitorName)
    {
        $this->conversation = $conversation;
        $this->messages = $conversation->messages()->orderBy('id', 'asc')->get();
        $this->projectName = $projectName;
        $this->visitorName = $visitorName;
    }

    public function build()
    {
        return $this->subject("Riwayat Chat Anda — {$this->projectName}")
            ->view('emails.chat-transcript');
    }
}
