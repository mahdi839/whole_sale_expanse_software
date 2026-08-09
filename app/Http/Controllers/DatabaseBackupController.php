<?php

namespace App\Http\Controllers;

use App\Services\MySqlDatabaseBackup;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function __invoke(MySqlDatabaseBackup $backup): BinaryFileResponse
    {
        try {
            $dumpPath = $backup->create();
        } catch (Throwable $exception) {
            report($exception);

            abort(500, 'The database backup could not be created. Please check the server log.');
        }

        $appName = Str::slug(config('app.name')) ?: 'database';
        $filename = sprintf('%s-database-%s.sql', $appName, now()->format('Y-m-d_H-i-s'));

        return response()
            ->download($dumpPath, $filename, [
                'Content-Type' => 'application/sql',
                'Cache-Control' => 'no-store, private',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend(true);
    }
}
