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
        // Check if we have actual Drive credentials in .env (Skipping for now as per plan)
        // For now, we simulate by storing in the 'public' disk.

        $path = $file->store($folderName, 'public');

        // Return a full URL so it looks like an external link
        return Storage::disk('public')->url($path);
    }

    /**
     * Upload raw content (string) as a file.
     *
     * @param string $content
     * @param string $filename
     * @param string $folderName
     * @return string The public URL of the uploaded file
     */
    public function uploadContent(string $content, string $filename, string $folderName = 'uploads'): string
    {
        $path = $folderName . '/' . $filename;
        Storage::disk('public')->put($path, $content);

        return Storage::disk('public')->url($path);
    }

    /**
     * Delete a file from "Google Drive".
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $url): bool
    {
        // Extract relative path from URL if possible, or just ignore for mock
        // simple simulation:
        return true;
    }
}
