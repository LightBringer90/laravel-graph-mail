<?php

namespace ProgressiveStudios\GraphMail\View\Components\Mail;

use Illuminate\View\Component;

class MailLayout extends Component
{
    public ?string $title;

    public function __construct($title = null)
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('graph-mail::components.mail.layout');
    }
}
