<?php

namespace GGPHP\User\Jobs;

use GGPHP\Contribute\Mail\ContributeApproval;
use GGPHP\Request\Mail\RequestApproval;
use GGPHP\User\Mail\NotificationPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data, $event)
    {
        $this->data = $data;
        $this->event = $event;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->event) {
            case 'REQUEST_APPROVAL':
                $mail = new RequestApproval($this->data);
                break;
            case 'NOTI_PASSWORD':
                $mail = new NotificationPassword($this->data);
                break;
            case 'CONTRIBUTE_APPROVAL':
                $mail = new ContributeApproval($this->data);
                break;
        }

        Mail::to($this->data['email'])->send($mail);

        if (Mail::failures()) {
            dispatch(new SendEmail($this->data, Mail::failures(), $this->event))->delay(10);
        }
    }
}
