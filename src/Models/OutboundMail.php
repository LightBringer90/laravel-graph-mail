<?php

namespace ProgressiveStudios\GraphMail\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundMail extends Model
{
    protected $table = 'outbound_mails';

    protected $fillable
        = [
            'sender_upn',
            'subject',
            'template_key',
            'template_data',
            'to_recipients',
            'cc_recipients',
            'bcc_recipients',
            'html_body',
            'sent_at',
            'status',
            'attachments',
        ];

    protected $casts
        = [
            'to_recipients'  => 'array',
            'cc_recipients'  => 'array',
            'bcc_recipients' => 'array',
            'template_data'  => 'array',
            'attachments'    => 'array',
            'sent_at'        => 'datetime',
        ];
}
