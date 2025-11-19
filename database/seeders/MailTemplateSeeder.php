<?php

namespace ProgressiveStudios\GraphMail\Database\Seeders;

use Illuminate\Database\Seeder;
use ProgressiveStudios\GraphMail\Models\MailTemplate;

class MailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        MailTemplate::firstOrCreate(
            ['key' => 'welcome.user'],
            [
                'name'            => 'Welcome (User)',
                'module'          => 'users',
                'view'            => 'graph-mail::emails.welcome',
                'default_subject' => 'Welcome aboard',
                'default_data'    => ['title' => 'Welcome!'],
                'active'          => true,
            ]
        );
    }
}
