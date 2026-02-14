<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to S3 and prune old backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $filename = "backup-" . now()->format('Y-m-d-His') . ".sql.gz";
        $tempPath = storage_path('app/' . $filename);

        $this->info("Starting backup for database: {$database}...");

        // 1. Create compressed backup using mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s | gzip > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($tempPath)
        );

        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Backup failed with exit code: {$returnVar}");
            if (file_exists($tempPath))
                unlink($tempPath);
            return 1;
        }

        $this->info("Backup file created locally: {$filename}");

        // 2. Upload to S3 (backups disk)
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('backups');
            $stream = fopen($tempPath, 'r');
            $disk->put($filename, $stream);
            if (is_resource($stream))
                fclose($stream);

            $this->info("Backup uploaded to S3 successfully.");
        } catch (\Exception $e) {
            $this->error("Upload to S3 failed: " . $e->getMessage());
            if (file_exists($tempPath))
                unlink($tempPath);
            return 1;
        }

        // 3. Prune old backups (30 days)
        $this->pruneOldBackups($disk);

        // 4. Cleanup local temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $this->info("Database backup process completed.");
        return 0;
    }

    protected function pruneOldBackups($disk)
    {
        $this->info("Pruning backups older than 30 days...");

        $files = $disk->files();
        $now = now();
        $deletedCount = 0;

        foreach ($files as $file) {
            // Check if file matches our pattern backup-YYYY-MM-DD-HHiiss.sql.gz
            if (preg_match('/backup-(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                $fileDate = \Carbon\Carbon::parse($matches[1]);
                if ($fileDate->diffInDays($now) > 30) {
                    $disk->delete($file);
                    $this->line("Deleted old backup: {$file}");
                    $deletedCount++;
                }
            }
        }

        $this->info("Pruning completed. Deleted {$deletedCount} files.");
    }
}
