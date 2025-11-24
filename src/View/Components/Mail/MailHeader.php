<?php

namespace ProgressiveStudios\GraphMail\View\Components\Mail;

use Illuminate\View\Component;

class MailHeader extends Component
{
    public string $title;

    public function __construct($title = 'Default Title')
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('graph-mail::components.mail.header');
    }
}
