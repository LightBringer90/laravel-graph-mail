<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Support\MailPayloadValidator;
use ProgressiveStudios\GraphMail\Services\OutboundMailService;

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
            // 🔹 validate payload (excluding file objects)
            $data = MailPayloadValidator::validate($req->all());
        } catch (ValidationException $e) {
            // Let the client know exactly what they did wrong
            return response()->json([
                'message' => 'Invalid email payload.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $attachments = [];

            if ($req->hasFile('attachments')) {
                foreach ($req->file('attachments') as $file) {
                    if (!$file->isValid()) {
                        throw new \RuntimeException(
                            'Invalid uploaded attachment: '.$file->getClientOriginalName()
                        );
                    }

                    $originalName = $file->getClientOriginalName();
                    $userId       = $req->user()->id ?? 'default';
                    $folder       = "graph-mail/outbound_attachments/{$userId}";

                    // 🔹 operator precedence bug avoided: group folder + userId
                    $filename = $this->uniqueFilename($folder, $originalName);

                    $path = $file->storeAs($folder, $filename);

                    $attachments[] = [
                        'path'     => $path,
                        'filename' => $filename,
                        'mime'     => $file->getClientMimeType(),
                        'size'     => $file->getSize(),
                    ];
                }
            }

            // 🔹 use injected service
            $mail = $this->mailService->queueMail($data, $attachments);

            return response()->json(
                ['id' => $mail->id, 'status' => $mail->status],
                202
            );

        } catch (\Throwable $e) {
            Log::error('Failed to queue outbound mail', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to queue email. Please try again later.',
            ], 500);
        }
    }

    /**
     * Generate a unique filename inside a folder.
     *
     * Keeps original name, appends " (1)", " (2)", ... if needed.
     */
    protected function uniqueFilename(string $folder, string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $ext  = pathinfo($originalName, PATHINFO_EXTENSION);

        $candidate = $originalName;
        $counter   = 1;

        while (Storage::exists($folder.'/'.$candidate)) {
            if ($ext !== '') {
                $candidate = $name.' ('.$counter.').'.$ext;
            } else {
                $candidate = $name.' ('.$counter.')';
            }
            $counter++;
        }

        return $candidate;
    }
}
