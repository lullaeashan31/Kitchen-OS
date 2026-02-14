<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveOldSopData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sop:archive-old-data {--days=90 : Number of days of data to keep}';

    protected $description = 'Moves SOP photos and reports older than X days to an archive folder on S3.';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        $this->info("Archiving SOP data older than {$days} days (Cutoff: {$cutoffDate->toDateString()})...");

        $disk = \Illuminate\Support\Facades\Storage::disk('s3');
        $dirsToArchive = ['sop/photos', 'sop/daily-reports'];

        foreach ($dirsToArchive as $baseDir) {
            $this->archiveDirectory($disk, $baseDir, $cutoffDate);
        }

        $this->info("Archiving process completed.");
    }

    private function archiveDirectory($disk, $dir, $cutoffDate)
    {
        if (!$disk->exists($dir)) {
            $this->warn("Directory {$dir} does not exist on S3.");
            return;
        }

        $files = $disk->allFiles($dir);
        $count = 0;

        foreach ($files as $file) {
            try {
                $lastModified = \Carbon\Carbon::createFromTimestamp($disk->lastModified($file));

                if ($lastModified->lt($cutoffDate)) {
                    $archivePath = 'sop/archive/' . $file;
                    $disk->move($file, $archivePath);
                    $count++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to archive file {$file}: " . $e->getMessage());
            }
        }

        $this->info("Moved {$count} files from {$dir} to archive.");
    }
}
