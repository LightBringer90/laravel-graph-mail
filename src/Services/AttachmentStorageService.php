<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class AttachmentStorageService
{
    public function __construct(
        protected ?Filesystem $disk = null,
        protected ?string $diskName = null,
    ) {
        // Allow override via DI / config, default to 'local'
        $this->diskName = $this->diskName ?? config('graph-mail.attachments_disk', 'local');
        $this->disk     = $this->disk ?? Storage::disk($this->diskName);
    }

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
        if (empty($attachments)) {
            return [];
        }

        $folderRelative = "graph-mail/outbound_attachments/{$mail->id}";
        $this->ensureDirectoryExists($folderRelative);

        $saved = [];

        foreach ($attachments as $descriptor) {
            $meta = $this->storeSingleAttachment($folderRelative, $descriptor);

            if ($meta !== null) {
                $saved[] = $meta;
            }
        }

        return $saved;
    }

    protected function storeSingleAttachment(string $folderRelative, array $descriptor): ?array
    {
        // Case 1: HTTP upload
        if (($descriptor['uploaded_file'] ?? null) instanceof UploadedFile) {
            return $this->storeUploadedFile($folderRelative, $descriptor['uploaded_file']);
        }

        // Case 2: base64 from RabbitMQ or similar
        if (isset($descriptor['content_base64'], $descriptor['filename'])) {
            return $this->storeBase64Attachment($folderRelative, $descriptor);
        }

        graph_mail_logger()->warning('graph-mail.attachment_descriptor_unrecognized', [
            'descriptor' => $descriptor,
        ]);

        return null;
    }

    protected function storeUploadedFile(string $folderRelative, UploadedFile $file): ?array
    {
        if (!$file->isValid()) {

            graph_mail_logger()->warning('graph-mail.attachment_invalid_upload', [
                'name' => $file->getClientOriginalName(),
            ]);

            return null;
        }

        $originalName = $file->getClientOriginalName();
        $mime         = $file->getClientMimeType() ?: 'application/octet-stream';

        $filename = $this->uniqueFilename($folderRelative, $originalName);

        // NOTE: this returns a *relative* path on the disk root
        $path = $file->storeAs($folderRelative, $filename, $this->diskName);

        if (!$path) {
            graph_mail_logger()->error('graph-mail.attachment_store_failed_upload', [
                'filename' => $originalName
            ]);

            return null;
        }

        $size = $file->getSize();

        return [
            'path'     => $path,
            'filename' => $filename,
            'mime'     => $mime,
            'size'     => $size,
        ];
    }

    protected function storeBase64Attachment(string $folderRelative, array $descriptor): ?array
    {
        $originalName = $descriptor['filename'];
        $mime         = $descriptor['mime'] ?? 'application/octet-stream';

        $binary = base64_decode($descriptor['content_base64'], true);
        if ($binary === false) {
            graph_mail_logger()->warning('graph-mail.attachment_invalid_base64', [
                'filename' => $originalName
            ]);

            return null;
        }

        $filename     = $this->uniqueFilename($folderRelative, $originalName);
        $relativePath = $folderRelative.'/'.$filename;

        // Write to the configured disk
        $stored = $this->disk->put($relativePath, $binary);

        if (!$stored) {
            graph_mail_logger()->error('graph-mail.attachment_store_failed_base64', [
                'filename' => $originalName
            ]);

            return null;
        }

        $size = $this->disk->size($relativePath);

        graph_mail_logger()->info('graph-mail.attachment_base64_stored', [
            'disk'          => $this->diskName,
            'relative_path' => $relativePath,
            'size'          => $size,
        ]);

        return [
            'path'          => $relativePath,
            'filename'      => $filename,
            'mime'          => $mime,
            'size'          => $size,
        ];
    }

    protected function ensureDirectoryExists(string $folderRelative): void
    {
        if (!$this->disk->exists($folderRelative)) {
            $this->disk->makeDirectory($folderRelative);
        }
    }

    /**
     * Generate a unique filename inside a folder.
     *
     * Keeps the original name, appends "(1)", "(2)", ... if needed.
     */
    protected function uniqueFilename(string $folderRelative, string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $ext  = pathinfo($originalName, PATHINFO_EXTENSION);

        $candidate = $originalName;
        $counter   = 1;

        while ($this->disk->exists($folderRelative.'/'.$candidate)) {
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
