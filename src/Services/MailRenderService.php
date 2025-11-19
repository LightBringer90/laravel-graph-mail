<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Support\Facades\View;
use ProgressiveStudios\GraphMail\Models\MailTemplate;

class MailRenderService
{
    public function render(?string $templateKey, ?array $data, ?string $fallbackHtml): array
    {
        if ($templateKey) {
            $tpl = MailTemplate::where('key', $templateKey)->where('active', true)->first();
            if ($tpl) {
                if ($class = $tpl->mailable_class) {
                    if (class_exists($class)) {
                        $mailable = app($class, ['data' => array_merge($tpl->default_data ?? [], $data ?? [])]);
                        if (method_exists($mailable, 'render')) {
                            $html = (string) $mailable->render();
                            $subject = property_exists($mailable, 'subject') ? ($mailable->subject ??
                                $tpl->default_subject) : ($tpl->default_subject);
                            return [$subject, $html];
                        }
                    }
                }
                if ($tpl->view && View::exists($tpl->view)) {
                    $merged = array_merge($tpl->default_data ?? [], $data ?? []);
                    $html = View::make($tpl->view, $merged)->render();
                    $subject = $merged['subject'] ?? $tpl->default_subject;
                    return [$subject, $html];
                }
            }
        }
        return [null, $fallbackHtml ?? '<p></p>'];
    }
}
