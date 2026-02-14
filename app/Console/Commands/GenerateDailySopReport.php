<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDailySopReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sop:generate-daily-report {--date= : The date to generate the report for (YYYY-MM-DD)}';

    protected $description = 'Generates a daily SOP compliance report PDF and saves it to S3.';

    public function handle()
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();
        $this->info("Generating SOP report for {$date}...");

        $runs = \App\Models\SopDailyRun::with(['checklist', 'completions.item', 'user'])
            ->whereDate('date', $date)
            ->get();

        if ($runs->isEmpty()) {
            $this->warn("No SOP runs found for {$date}. Skipping report.");
            return;
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sop.report_daily', [
                'runs' => $runs,
                'date' => $date
            ]);

            $pdfContent = $pdf->output();
            $fileName = "sop/daily-reports/{$date}.pdf";

            // Save to S3
            \Illuminate\Support\Facades\Storage::disk('s3')->put($fileName, $pdfContent);

            $this->info("Report generated and saved to S3: {$fileName}");

            // Optional: Also save locally for backup if configured
            if (config('filesystems.default') !== 's3') {
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $pdfContent);
                $this->info("Report also saved to local public disk.");
            }

        } catch (\Exception $e) {
            $this->error("Failed to generate report: " . $e->getMessage());
            \Log::error("SOP Daily Report Generation Error: " . $e->getMessage());
        }
    }
}
