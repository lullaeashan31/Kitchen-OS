<?php

namespace App\Services;

use App\Models\DriveFile;
use App\Models\User;
use App\Enums\LinkedType;
use Illuminate\Database\Eloquent\Model;

class DriveFileService
{
    /**
     * Attach a Google Drive file to a model using a URL.
     */
    public function attachUrl(
        Model $linkedModel,
        string $url,
        string $name,
        User $user
    ): DriveFile {
        $fileId = DriveFile::extractFileId($url);

        if (!$fileId) {
            throw new \InvalidArgumentException('Invalid Google Drive URL');
        }

        $linkedType = match (get_class($linkedModel)) {
            \App\Models\Recipe::class => LinkedType::Recipe,
            \App\Models\ProductionDay::class => LinkedType::Production,
            \App\Models\Ingredient::class => LinkedType::Ingredient,
            default => throw new \InvalidArgumentException('Unsupported model type'),
        };

        return DriveFile::create([
            'name' => $name,
            'drive_url' => $url,
            'file_id' => $fileId,
            'linked_type' => $linkedType,
            'linked_id' => $linkedModel->id,
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Attach a file upload to a model.
     */
    public function attachFile(
        Model $linkedModel,
        \Illuminate\Http\UploadedFile $file,
        string $name,
        User $user
    ): DriveFile {
        $path = $file->store('drive_files', 'public');

        $linkedType = match (get_class($linkedModel)) {
            \App\Models\Recipe::class => LinkedType::Recipe,
            \App\Models\ProductionDay::class => LinkedType::Production,
            \App\Models\Ingredient::class => LinkedType::Ingredient,
            default => throw new \InvalidArgumentException('Unsupported model type'),
        };

        return DriveFile::create([
            'name' => $name,
            'drive_url' => null,
            'path' => $path,
            'file_id' => 'file_' . uniqid(),
            'linked_type' => $linkedType,
            'linked_id' => $linkedModel->id,
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Delete a drive file link.
     */
    public function deleteFile(DriveFile $driveFile): bool
    {
        return $driveFile->delete();
    }
}
