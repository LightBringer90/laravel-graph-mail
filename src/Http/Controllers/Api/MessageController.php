<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Jobs\SendGraphMailJob;

class MessageController extends Controller
{
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
        // Validation – Laravel returns 422 automatically on failure
        $data = $req->validate([
            'sender'        => 'nullable|email',
            'subject'       => 'sometimes|string|nullable',
            'template_key'  => 'sometimes|string|nullable',
            'data'          => 'sometimes|array',
            'html'          => 'sometimes|string|nullable',
            'to'            => 'required|array|min:1',
            'to.*'          => 'email',
            'cc'            => 'sometimes|array',
            'cc.*'          => 'email',
            'bcc'           => 'sometimes|array',
            'bcc.*'         => 'email',
            'attachments'   => 'sometimes|array',
            'attachments.*' => 'file|max:10240',
        ]);

        try {
            return DB::transaction(function () use ($data, $req) {
                $sender = $data['sender'] ?? config('graph-mail.default_sender');

                $m = OutboundMail::create([
                    'sender_upn'     => $sender,
                    'subject'        => $data['subject'] ?? null,
                    'template_key'   => $data['template_key'] ?? null,
                    'template_data'  => $data['data'] ?? [],
                    'to_recipients'  => $data['to'],
                    'cc_recipients'  => $data['cc'] ?? [],
                    'bcc_recipients' => $data['bcc'] ?? [],
                    'html_body'      => $data['html'] ?? null,
                    'status'         => 'queued',
                ]);

                $attachments = [];

                if ($req->hasFile('attachments')) {
                    foreach ($req->file('attachments') as $file) {
                        if (!$file->isValid()) {
                            throw new \RuntimeException(
                                'Invalid uploaded attachment: '.$file->getClientOriginalName()
                            );
                        }

                        $originalName = $file->getClientOriginalName();
                        $folder       = 'graph-mail/outbound_attachments/'.$m->id;

                        // Build a unique, human-friendly filename
                        $filename = $this->uniqueFilename($folder, $originalName);

                        // Store using the computed filename
                        $path = $file->storeAs($folder, $filename);

                        $attachments[] = [
                            'path'     => $path,
                            'filename' => $filename,
                            'mime'     => $file->getClientMimeType(),
                            'size'     => $file->getSize(),
                        ];
                    }
                }

                $m->attachments = $attachments;
                $m->save();

                SendGraphMailJob::dispatch($m->id);

                return response()->json(
                    ['id' => $m->id, 'status' => $m->status],
                    202
                );
            });
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
