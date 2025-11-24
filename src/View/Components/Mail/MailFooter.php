<?php

namespace ProgressiveStudios\GraphMail\View\Components\Mail;

use Illuminate\View\Component;

class MailFooter extends Component
{
    public function __construct()
    {

    }

    public function render()
    {
        return view('graph-mail::components.mail.footer');
    }
}
