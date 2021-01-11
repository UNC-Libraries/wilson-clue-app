<?php

namespace App\Mail;

use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $pieces = array_filter(preg_split('/(\n|\r\n?)/', $message, NULL, PREG_SPLIT_DELIM_CAPTURE), function ($line){
            return !empty(trim($line, " \t\0\x0B"));
        });
        $subject = $pieces[0];
        unset($pieces[0]);
        $body = implode('', $pieces);

        return $this->markdown('emails.registered', ['body' => $body])->subject($subject);
    }
}
