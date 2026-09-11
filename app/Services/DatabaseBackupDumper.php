<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use ZipArchive;

class DatabaseBackupDumper
{
    /**
     * Write a zip archive containing a SQL dump of the default database.
     */
    public function dumpToZip(string $zipPath): void
    {
        $sqlPath = $this->temporaryPath('database.sql');

        try {
            $this->dumpToSqlFile($sqlPath);

            if (! File::exists($sqlPath) || File::size($sqlPath) === 0) {
                throw new RuntimeException('Database dump is empty.');
            }

            $this->zipFile($sqlPath, 'database.sql', $zipPath);
        } finally {
            File::delete($sqlPath);
        }
    }

    private function dumpToSqlFile(string $path): void
    {
        $driver = DB::connection()->getDriverName();

        match ($driver) {
            'sqlite' => $this->dumpSqlite($path),
            'mysql', 'mariadb' => $this->dumpMysql($path),
            'pgsql' => $this->dumpPgsql($path),
            default => throw new RuntimeException("Unsupported database driver [{$driver}]."),
        };
    }

    private function dumpSqlite(string $path): void
    {
        $handle = $this->openHandle($path);

        try {
            fwrite($handle, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n");

            $objects = DB::select(
                "SELECT type, name, sql FROM sqlite_master WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY CASE type WHEN 'table' THEN 0 WHEN 'index' THEN 1 WHEN 'trigger' THEN 2 ELSE 3 END, name",
            );

            $tableNames = [];

            foreach ($objects as $object) {
                $sql = is_string($object->sql ?? null) ? $object->sql : null;
                $name = is_string($object->name ?? null) ? $object->name : null;
                $type = is_string($object->type ?? null) ? $object->type : null;

                if ($sql === null || $name === null) {
                    continue;
                }

                fwrite($handle, $sql.";\n");

                if ($type === 'table') {
                    $tableNames[] = $name;
                }
            }

            foreach ($tableNames as $table) {
                $this->writeInserts($handle, $table);
            }

            fwrite($handle, "COMMIT;\n");
        } finally {
            fclose($handle);
        }
    }

    private function dumpMysql(string $path): void
    {
        if ($this->dumpMysqlNative($path)) {
            return;
        }

        $this->dumpMysqlWithPhp($path);
    }

    private function dumpMysqlNative(string $path): bool
    {
        $config = DB::connection()->getConfig();
        $defaultsPath = $this->writeMysqlDefaultsFile($config);

        $handle = $this->openHandle($path);

        try {
            $command = [
                'mysqldump',
                '--defaults-extra-file='.$defaultsPath,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--no-tablespaces',
                '--skip-lock-tables',
                (string) ($config['database'] ?? ''),
            ];

            $result = Process::timeout((int) config('backup.timeout'))
                ->run($command, function (string $type, string $output) use ($handle): void {
                    if ($type === 'out') {
                        fwrite($handle, $output);
                    }
                });
        } finally {
            fclose($handle);
            File::delete($defaultsPath);
        }

        if (! $result->successful() || ! File::exists($path) || File::size($path) === 0) {
            File::delete($path);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function writeMysqlDefaultsFile(array $config): string
    {
        $path = $this->temporaryPath('my.cnf');
        $password = str_replace(['\\', '"'], ['\\\\', '\"'], (string) ($config['password'] ?? ''));

        $contents = "[client]\n"
            .'user="'.str_replace(['\\', '"'], ['\\\\', '\"'], (string) ($config['username'] ?? ''))."\"\n"
            ."password=\"{$password}\"\n"
            .'host="'.str_replace(['\\', '"'], ['\\\\', '\"'], (string) ($config['host'] ?? '127.0.0.1'))."\"\n"
            .'port="'.(string) ($config['port'] ?? '3306')."\"\n";

        $socket = $config['unix_socket'] ?? null;

        if (is_string($socket) && $socket !== '') {
            $contents .= 'socket="'.str_replace(['\\', '"'], ['\\\\', '\"'], $socket)."\"\n";
        }

        File::put($path, $contents);
        chmod($path, 0600);

        return $path;
    }

    private function dumpMysqlWithPhp(string $path): void
    {
        $handle = $this->openHandle($path);

        try {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n");

            foreach ($this->tableNames() as $table) {
                $quoted = $this->quoteIdentifier($table);
                $createRows = DB::select("SHOW CREATE TABLE {$quoted}");
                $createRow = isset($createRows[0]) ? (array) $createRows[0] : [];
                $statement = $createRow['Create Table'] ?? $createRow['Create View'] ?? null;

                if (! is_string($statement) || $statement === '') {
                    throw new RuntimeException("Unable to read schema for table [{$table}].");
                }

                fwrite($handle, "DROP TABLE IF EXISTS {$quoted};\n{$statement};\n");
                $this->writeInserts($handle, $table);
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    private function dumpPgsql(string $path): void
    {
        $config = DB::connection()->getConfig();

        $result = Process::timeout((int) config('backup.timeout'))
            ->env([
                'PGPASSWORD' => (string) ($config['password'] ?? ''),
            ])
            ->run([
                'pg_dump',
                '--host='.(string) ($config['host'] ?? '127.0.0.1'),
                '--port='.(string) ($config['port'] ?? '5432'),
                '--username='.(string) ($config['username'] ?? ''),
                '--dbname='.(string) ($config['database'] ?? ''),
                '--no-owner',
                '--no-acl',
                '--format=plain',
            ], function (string $type, string $output) use ($path): void {
                if ($type === 'out') {
                    File::append($path, $output);
                }
            });

        if ($result->successful() && File::exists($path) && File::size($path) > 0) {
            return;
        }

        File::delete($path);

        throw new RuntimeException('Unable to dump the PostgreSQL database. Ensure pg_dump is installed.');
    }

    /**
     * @param  resource  $handle
     */
    private function writeInserts($handle, string $table): void
    {
        $quotedTable = $this->quoteIdentifier($table);
        $pdo = DB::connection()->getPdo();

        foreach (DB::table($table)->cursor() as $row) {
            $values = [];

            foreach ((array) $row as $value) {
                $values[] = $this->quoteValue($pdo, $value);
            }

            $columns = collect(array_keys((array) $row))
                ->map(fn (int|string $column): string => $this->quoteIdentifier((string) $column))
                ->implode(', ');

            fwrite($handle, "INSERT INTO {$quotedTable} ({$columns}) VALUES (".implode(', ', $values).");\n");
        }
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_resource($value)) {
            $contents = stream_get_contents($value);

            return $pdo->quote($contents === false ? '' : $contents);
        }

        return $pdo->quote((string) $value);
    }

    /**
     * @return list<string>
     */
    private function tableNames(): array
    {
        return array_values(Schema::getTableListing(
            Schema::getCurrentSchemaListing(),
            schemaQualified: false,
        ));
    }

    private function quoteIdentifier(string $name): string
    {
        $driver = DB::connection()->getDriverName();
        $quote = in_array($driver, ['mysql', 'mariadb'], true) ? '`' : '"';

        return $quote.str_replace($quote, $quote.$quote, $name).$quote;
    }

    /**
     * @return resource
     */
    private function openHandle(string $path)
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new RuntimeException('Unable to write database dump.');
        }

        return $handle;
    }

    private function zipFile(string $sourcePath, string $entryName, string $zipPath): void
    {
        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive;
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            throw new RuntimeException('Unable to create backup archive.');
        }

        if (! $zip->addFile($sourcePath, $entryName)) {
            $zip->close();
            File::delete($zipPath);

            throw new RuntimeException('Unable to add dump to backup archive.');
        }

        if (! $zip->close()) {
            File::delete($zipPath);

            throw new RuntimeException('Unable to finalize backup archive.');
        }
    }

    private function temporaryPath(string $basename): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .'sdms-backup-'.Str::uuid()->toString().'-'.$basename;
    }
}
