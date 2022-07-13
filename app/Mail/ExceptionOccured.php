<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExceptionOccured extends Mailable
{
    use Queueable, SerializesModels;

    public $html;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($html)
    {
        //
        $this->html = $html;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = array('html'=>$this->html);
        return $this->view('email.exception',$data);
    }
}
