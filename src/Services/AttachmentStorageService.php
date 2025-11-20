<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ProgressiveStudios\GraphMail\Models\OutboundMail;

class AttachmentStorageService
{
    /**
     * @param  OutboundMail  $mail
     * @param  array  $attachments  list of "descriptors".
     *                              Each element can be:
     *                              - ['uploaded_file' => UploadedFile]
     *                              - ['filename' => string, 'mime' => string|null, 'content_base64' => string]
     *
     * @return array  normalized attachments:
     *                ['path' => ..., 'filename' => ..., 'mime' => ..., 'size' => ...]
     */
    public function storeForMail(OutboundMail $mail, array $attachments): array
    {
        $saved = [];

        $disk           = Storage::disk('local');
        $folderRelative = "graph-mail/outbound_attachments/{$mail->id}";

        if (!$disk->exists($folderRelative)) {
            $disk->makeDirectory($folderRelative);
        }

        foreach ($attachments as $att) {
            // Case 1: HTTP upload
            if (isset($att['uploaded_file']) && $att['uploaded_file'] instanceof UploadedFile) {
                /** @var UploadedFile $file */
                $file = $att['uploaded_file'];

                if (!$file->isValid()) {
                    Log::warning('graph-mail.attachment_invalid_upload', [
                        'name' => $file->getClientOriginalName(),
                    ]);
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $mime         = $file->getClientMimeType() ?? 'application/octet-stream';

                $filename = $this->uniqueFilename($folderRelative, $originalName);

                $path = $file->storeAs($folderRelative, $filename, 'local');

                if (!$path) {
                    Log::error('graph-mail.attachment_store_failed_upload', [
                        'filename' => $originalName,
                    ]);
                    continue;
                }

                $size = $file->getSize();
            }

            // Case 2: base64 from RabbitMQ
            elseif (isset($att['content_base64'], $att['filename'])) {
                $originalName = $att['filename'];
                $mime         = $att['mime'] ?? 'application/octet-stream';

                $binary = base64_decode($att['content_base64'], true);
                if ($binary === false) {
                    Log::warning('graph-mail.attachment_invalid_base64', [
                        'filename' => $originalName,
                    ]);
                    continue;
                }

                $filename     = $this->uniqueFilename($folderRelative, $originalName);
                $relativePath = $folderRelative.'/'.$filename;

                $stored = $disk->put($relativePath, $binary);
                if (!$stored) {
                    Log::error('graph-mail.attachment_store_failed_base64', [
                        'filename' => $originalName,
                    ]);
                    continue;
                }

                $path = $relativePath;
                $size = $disk->size($relativePath);
            }

            // Unknown form → skip
            else {
                Log::warning('graph-mail.attachment_descriptor_unrecognized', [
                    'descriptor' => $att,
                ]);
                continue;
            }

            $saved[] = [
                'path'     => $path,
                'filename' => $filename,
                'mime'     => $mime,
                'size'     => $size,
            ];
        }

        return $saved;
    }

    /**
     * Generate a unique filename inside a folder.
     *
     * Keeps original name, appends " (1)", " (2)", ... if needed.
     */
    protected function uniqueFilename(string $folderRelative, string $originalName): string
    {
        $disk = Storage::disk('local');

        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $ext  = pathinfo($originalName, PATHINFO_EXTENSION);

        $candidate = $originalName;
        $counter   = 1;

        while ($disk->exists($folderRelative.'/'.$candidate)) {
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
