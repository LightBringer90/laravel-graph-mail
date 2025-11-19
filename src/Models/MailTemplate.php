<?php

namespace ProgressiveStudios\GraphMail\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_templates';

    protected $fillable = [
        'key',
        'name',
        'to',
        'cc',
        'bcc',
        'module',
        'mailable_class',
        'view',
        'default_subject',
        'default_data',
        'active',
    ];

    protected $casts = [
        'default_data' => 'array',
        'to'           => 'array',
        'cc'           => 'array',
        'bcc'          => 'array',
        'active'       => 'boolean',
    ];
}
