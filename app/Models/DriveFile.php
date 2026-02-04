<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriveFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'drive_url',
        'file_id',
        'linked_type',
        'linked_id',
        'uploaded_by',
    ];

    // Relationships
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function linked()
    {
        return $this->morphTo();
    }

    // Accessors
    public function getPreviewUrlAttribute()
    {
        return "https://drive.google.com/file/d/{$this->file_id}/preview";
    }

    public function getDownloadUrlAttribute()
    {
        return "https://drive.google.com/uc?export=download&id={$this->file_id}";
    }

    // Helper Methods
    public static function extractFileId(string $url): ?string
    {
        // Extract file ID from various Google Drive URL formats
        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9_-]+)/',
            '/id=([a-zA-Z0-9_-]+)/',
            '/\/open\?id=([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function validateDriveUrl(string $url): bool
    {
        return str_contains($url, 'drive.google.com');
    }
}
