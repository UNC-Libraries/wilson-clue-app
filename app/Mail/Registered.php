<?php

namespace App\Mail;

use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Markdown;

class Registered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($team, $email_text)
    {
        $this->team = $team;
        $this->email_text = $email_text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = str_replace('||game_date||',$this->team->game->start_time->format('l, F jS'),
            str_replace('||game_time||',$this->team->game->start_time->format('g:i A'),
                str_replace('||team_name||', e($this->team->name),
                    str_replace('||team_management_url||', route('enlist.teamManagement'), $this->email_text->message))));
        $pieces = array_filter(preg_split('/\n|\r\n?/', $message), function ($line){
            return !empty(trim($line));
        });
        $subject = $pieces[0];
        unset($pieces[0]);
        $message = array_map(function ($line) {
            return Markdown::parse($line);
        }, $pieces);
        $body = implode('', $message);

        return $this->view('emails.registered', ['body' => $body])->subject($subject);
    }
}
