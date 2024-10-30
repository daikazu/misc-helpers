<?php

namespace Daikazu\MiscHelpers\Commands;

use Illuminate\Console\Command;

class PruneLogFilesCommand extends Command
{
    protected $signature = 'app:prune-log-files {--days=7 : Number of days before logs are deleted}';
    protected $description = 'Prune old log files to prevent them from taking up too much space.';

    public function handle(): void
    {
        $days = $this->option('days');
        //        $logPath = app()->storagePath('logs');
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log*'); // Adjust the pattern as needed

        $filesDeleted = 0;
        $totalFreedSpace = 0; // Initialize total freed space counter

        foreach ($files as $file) {
            if (is_file($file) && time() - filemtime($file) >= $days * 24 * 60 * 60) {
                $fileSize = filesize($file); // Get the file size before deletion
                if (unlink($file)) {
                    $this->info("Deleted log file: {$file}");
                    $filesDeleted++;
                    $totalFreedSpace += $fileSize; // Accumulate the freed space
                } else {
                    $this->error("Failed to delete log file: {$file}");
                }
            }
        }

        // Format the total freed space for display
        $freedSpaceInMb = number_format($totalFreedSpace / (1024 * 1024), 2); // Convert bytes to megabytes

        $this->info("Log files cleaned up! {$filesDeleted} files deleted. Freed up {$freedSpaceInMb} MB of space.");
    }
}
