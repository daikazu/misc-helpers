<?php

use Daikazu\MiscHelpers\Commands\PruneLogFilesCommand;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Set up the environment for the tests
    $this->logPath = storage_path('logs');
    File::ensureDirectoryExists($this->logPath);
    File::cleanDirectory($this->logPath);
});

afterEach(function () {
    // Clean up after the tests
    File::cleanDirectory($this->logPath);
});

it('deletes log files older than the specified number of days', function () {
    // Create a log file older than 7 days
    $oldLogFile = $this->logPath . '/old.log';
    File::put($oldLogFile, 'Old log content');
    touch($oldLogFile, now()->subDays(8)->timestamp);

    // Create a log file newer than 7 days
    $newLogFile = $this->logPath . '/new.log';
    File::put($newLogFile, 'New log content');
    touch($newLogFile, now()->subDays(6)->timestamp);

    // Run the command
    artisan(PruneLogFilesCommand::class, ['--days' => 7])
        ->assertExitCode(0);

    // Assert the old log file is deleted and the new log file is not deleted
    expect(File::exists($oldLogFile))->toBeFalse()
        ->and(File::exists($newLogFile))->toBeTrue();
});

it('outputs the correct number of deleted files and freed space', function () {
    $oldLogFile1 = $this->logPath . '/old1.log';
    $oldLogFile2 = $this->logPath . '/old2.log';
    File::put($oldLogFile1, str_repeat('A', 1024 * 1024)); // 1 MB
    File::put($oldLogFile2, str_repeat('B', 1024 * 1024)); // 1 MB
    touch($oldLogFile1, now()->subDays(8)->timestamp);
    touch($oldLogFile2, now()->subDays(8)->timestamp);

    artisan(PruneLogFilesCommand::class, ['--days' => 7])
        ->expectsOutputToContain('2 files deleted. Freed up 2.00 MB of space.')
        ->run();
});
