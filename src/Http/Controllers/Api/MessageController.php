<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Support\MailPayloadValidator;
use ProgressiveStudios\GraphMail\Services\OutboundMailService;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class MessageController extends Controller
{
    public function __construct(
        protected OutboundMailService $mailService,
    ) {}

    public function health()
    {
        return response()->json(['ok' => true, 'time' => now()->toIso8601String()]);
    }

    public function show($id)
    {
        $m = OutboundMail::findOrFail($id);

        return response()->json([
            'id'      => $m->id,
            'status'  => $m->status,
            'subject' => $m->subject,
            'sender'  => $m->sender_upn,
            'to'      => $m->to_recipients,
            'cc'      => $m->cc_recipients,
            'bcc'     => $m->bcc_recipients,
            'sent_at' => optional($m->sent_at)->toIso8601String(),
        ]);
    }

    public function send(Request $req)
    {
        try {
            $data = MailPayloadValidator::validate($req->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid email payload.',
                'errors'  => $e->errors(),
            ], 422);
        }

        // Build attachment descriptors (NO disk writes here)
        $attachmentDescriptors = [];
        if ($req->hasFile('attachments')) {
            foreach ($req->file('attachments') as $file) {
                $attachmentDescriptors[] = [
                    'uploaded_file' => $file,
                ];
            }
        }

        try {
            $mail = $this->mailService->queueMail($data, $attachmentDescriptors);

            return response()->json(
                ['id' => $mail->id, 'status' => $mail->status],
                202
            );
        } catch (\Throwable $e) {
            graph_mail_logger()->error('graph-mail.failed_to_queue_outbound_mail', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to queue email. Please try again later.',
            ], 500);
        }
    }
}
