<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup
                            {--database=mysql : Connexion à utiliser}
                            {--compress : Compresser le fichier}
                            {--upload : Uploader vers le cloud}';

    protected $description = 'Sauvegarde complète de la base de données';

    public function handle(): int
    {
        $connection = $this->option('database');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");

        if (!$database) {
            $this->error("Connexion '{$connection}' non trouvée dans config/database.php");
            return Command::FAILURE;
        }

        $filename = "gel-backup-{$database}-" . now()->format('Y-m-d-H-i-s') . ".sql";
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $path = "{$backupDir}/{$filename}";

        $this->info("💾 Sauvegarde de {$database}...");

        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s --routines --triggers --events > %s 2>/dev/null',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        $resultCode = 0;
        $output = [];
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error('❌ Échec de la sauvegarde. Vérifiez que mysqldump est installé et accessible.');
            return Command::FAILURE;
        }

        if ($this->option('compress')) {
            exec("gzip {$path}", $_, $resultCode);
            if ($resultCode === 0) {
                $filename .= '.gz';
                $path .= '.gz';
            }
        }

        $size = filesize($path);
        $sizeFormatted = round($size / 1024 / 1024, 2);

        $this->info("✅ Sauvegarde réussie : {$filename} ({$sizeFormatted} Mo)");

        // Nettoyage des backups de plus de 7 jours
        $oldBackups = glob("{$backupDir}/*.sql*");
        foreach ($oldBackups as $oldBackup) {
            if (filemtime($oldBackup) < now()->subDays(7)->timestamp) {
                unlink($oldBackup);
                $this->line("  🗑️  Ancien backup supprimé : " . basename($oldBackup));
            }
        }

        return Command::SUCCESS;
    }
}
