<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class MySqlDatabaseBackup
{
    public function create(): string
    {
        $connectionName = config('database.default');
        $database = config("database.connections.{$connectionName}");

        if (! is_array($database) || ($database['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Database backups are only configured for MySQL.');
        }

        foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
            if (! array_key_exists($key, $database)) {
                throw new RuntimeException("The MySQL {$key} configuration is missing.");
            }
        }

        $directory = storage_path('app/private/database-backups');
        File::ensureDirectoryExists($directory, 0750);

        $credentialsPath = tempnam($directory, 'mysql-credentials-');
        $dumpPath = tempnam($directory, 'database-backup-');

        if ($credentialsPath === false || $dumpPath === false) {
            $this->deleteFile($credentialsPath);
            $this->deleteFile($dumpPath);

            throw new RuntimeException('Unable to create temporary database backup files.');
        }

        try {
            $credentials = implode(PHP_EOL, [
                '[client]',
                'host='.$this->optionValue($database['host']),
                'port='.(int) $database['port'],
                'user='.$this->optionValue($database['username']),
                'password='.$this->optionValue($database['password']),
                '',
            ]);

            if (file_put_contents($credentialsPath, $credentials, LOCK_EX) === false) {
                throw new RuntimeException('Unable to prepare MySQL backup credentials.');
            }

            @chmod($credentialsPath, 0600);

            $process = new Process([
                config('backup.mysql_dump_binary'),
                '--defaults-extra-file='.$credentialsPath,
                '--single-transaction',
                '--quick',
                '--routines',
                '--events',
                '--triggers',
                '--hex-blob',
                '--no-tablespaces',
                '--default-character-set=utf8mb4',
                '--result-file='.$dumpPath,
                $database['database'],
            ], base_path());

            $process->setTimeout(config('backup.timeout'));
            $process->mustRun();

            if (! is_file($dumpPath) || filesize($dumpPath) === 0) {
                throw new RuntimeException('MySQL created an empty database backup.');
            }

            @chmod($dumpPath, 0600);

            return $dumpPath;
        } catch (Throwable $exception) {
            $this->deleteFile($dumpPath);

            throw $exception;
        } finally {
            $this->deleteFile($credentialsPath);
        }
    }

    private function optionValue(mixed $value): string
    {
        return '"'.addcslashes((string) $value, "\\\"\n\r").'"';
    }

    private function deleteFile(string|false $path): void
    {
        if (is_string($path) && is_file($path)) {
            @unlink($path);
        }
    }
}
