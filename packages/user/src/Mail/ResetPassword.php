<?php

namespace GGPHP\User\Mail;

use Illuminate\Mail\Mailable;

class ResetPassword extends Mailable
{

    /**
     * User name
     *
     * @var string
     */
    public $name;

    /**
     * URL in client site
     *
     * @var string
     */
    public $urlClient;

    /**
     * Create a new message instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->urlClient = $data['urlClient'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject(trans('lang-user::messages.email.reset.subject'))
            ->view('view-users::emails.forgot_password');
    }
}
