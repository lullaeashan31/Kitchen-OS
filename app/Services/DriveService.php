<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DriveService
{
    /**
     * Upload a file to "Google Drive" (Simulated locally).
     *
     * @param UploadedFile $file
     * @param string $folderName
     * @return string The public URL of the uploaded file
     */
    public function uploadFile(UploadedFile $file, string $folderName = 'uploads'): string
    {
        // Store on default disk
        return $file->store($folderName, config('filesystems.default'));
    }

    /**
     * Upload raw content (string) as a file.
     *
     * @param string $content
     * @param string $filename
     * @param string $folderName
     * @return string The path of the uploaded file
     */
    public function uploadContent(string $content, string $filename, string $folderName = 'uploads'): string
    {
        $disk = config('filesystems.default');
        $path = $folderName . '/' . $filename;
        Storage::disk($disk)->put($path, $content);

        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk(config('filesystems.default'))->delete($path);
    }
}
