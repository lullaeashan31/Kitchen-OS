<?php

namespace App\Services;

use App\Models\DriveFile;
use App\Models\User;
use App\Enums\LinkedType;
use Illuminate\Database\Eloquent\Model;

class DriveFileService
{
    /**
     * Attach a Google Drive file to a model.
     */
    public function attachFile(
        Model $linkedModel,
        string $driveUrl,
        string $name,
        User $user
    ): DriveFile {
        $fileId = DriveFile::extractFileId($driveUrl);

        if (!$fileId) {
            throw new \InvalidArgumentException('Invalid Google Drive Link');
        }

        // Determine linked type enum
        $linkedType = match (get_class($linkedModel)) {
            \App\Models\Recipe::class => LinkedType::Recipe,
            \App\Models\ProductionDay::class => LinkedType::Production,
            \App\Models\Ingredient::class => LinkedType::Ingredient,
            default => throw new \InvalidArgumentException('Unsupported model type'),
        };

        return DriveFile::create([
            'name' => $name,
            'drive_url' => $driveUrl,
            'file_id' => $fileId,
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
