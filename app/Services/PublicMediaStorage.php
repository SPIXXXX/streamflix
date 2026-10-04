<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PublicMediaStorage
{
    public function store(UploadedFile $file, string $directory): string
    {
        if (! config('services.r2.enabled')) {
            return $file->store($directory, 'public');
        }

        $key = trim($directory, '/').'/'.Str::uuid().'.'.($file->guessExtension() ?: 'bin');
        $stream = fopen($file->getRealPath(), 'rb');

        try {
            $this->client()->putObject([
                'Bucket' => $this->required('bucket'),
                'Key' => $key,
                'Body' => $stream,
                'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return rtrim($this->required('public_url'), '/').'/'.$key;
    }

    public function storeContents(string $contents, string $directory, string $extension, string $contentType): string
    {
        $key = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! config('services.r2.enabled')) {
            Storage::disk('public')->put($key, $contents);

            return $key;
        }

        $this->client()->putObject([
            'Bucket' => $this->required('bucket'),
            'Key' => $key,
            'Body' => $contents,
            'ContentType' => $contentType,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return rtrim($this->required('public_url'), '/').'/'.$key;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $publicUrl = rtrim((string) config('services.r2.public_url'), '/');
        if (config('services.r2.enabled') && $publicUrl !== '' && str_starts_with($path, $publicUrl.'/')) {
            $key = rawurldecode(substr($path, strlen($publicUrl) + 1));
            $this->client()->deleteObject([
                'Bucket' => $this->required('bucket'),
                'Key' => $key,
            ]);

            return;
        }

        if (! filter_var($path, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function client(): S3Client
    {
        $accountId = $this->required('account_id');

        return new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => 'https://'.$accountId.'.r2.cloudflarestorage.com',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $this->required('access_key_id'),
                'secret' => $this->required('secret_access_key'),
            ],
        ]);
    }

    private function required(string $key): string
    {
        $value = trim((string) config('services.r2.'.$key));

        if ($value === '') {
            throw new RuntimeException('Cloudflare R2 is enabled but the '.$key.' setting is missing.');
        }

        return $value;
    }
}
