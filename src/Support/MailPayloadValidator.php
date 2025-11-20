<?php

namespace ProgressiveStudios\GraphMail\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MailPayloadValidator
{
    public static function validate(array $payload): array
    {
        $rules = [
            'sender'       => 'nullable|email:rfc,dns',
            'subject'      => 'nullable|string',
            'template_key' => 'nullable|string',
            'data'         => 'array',
            'html'         => 'nullable|string',

            'to'   => 'required|array|min:1',
            'to.*' => 'email:rfc,dns',

            'cc'   => 'array',
            'cc.*' => 'email:rfc,dns',

            'bcc'   => 'array',
            'bcc.*' => 'email:rfc,dns',

            'attachments'  => 'array',
        ];

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
