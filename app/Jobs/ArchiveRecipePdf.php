<?php

namespace App\Jobs;

use App\Models\Recipe;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveRecipePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipe;

    /**
     * Create a new job instance.
     */
    public function __construct(Recipe $recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleDriveService $driveService): void
    {
        try {
            $fileId = $driveService->saveRecipePdfToDrive($this->recipe);
            if ($fileId) {
                $this->recipe->update(['drive_file_id' => $fileId]);
            }
        } catch (\Exception $e) {
            Log::error("Async Backup failed for recipe ID {$this->recipe->id}: " . $e->getMessage());
            throw $e; // Retry if failed
        }
    }
}
