<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:convert-webp {--dry : Jalankan tanpa menyimpan perubahan}';
    protected $description = 'Konversi gambar lama (User, Course, Transaction, WebsiteSetting) ke format WebP secara aman.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry');

        // Cek dukungan WebP terlebih dahulu
        if (!ImageService::supportsWebp()) {
            $this->warn('GD/WebP tidak tersedia di environment ini. Konversi akan dilewati.');
        }

        $this->info('Memulai migrasi konversi gambar ke WebP' . ($dryRun ? ' (DRY-RUN)' : ''));

        // Users - photo
        $this->section('Users');
        User::whereNotNull('photo')
            ->select(['id', 'photo'])
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($dryRun) {
                foreach ($users as $user) {
                    $path = $user->photo;
                    if (!$path || Str::startsWith($path, ['http://', 'https://'])) {
                        continue; // Skip URL eksternal
                    }
                    $converted = ImageService::convertToWebp($path, 'public', 85, true);
                    if ($converted !== $path) {
                        $this->line("User #{$user->id}: {$path} -> {$converted}");
                        if (!$dryRun) {
                            User::where('id', $user->id)->update(['photo' => $converted]);
                        }
                    }
                }
            });

        // Courses - thumbnail
        $this->section('Courses');
        Course::whereNotNull('thumbnail')
            ->select(['id', 'thumbnail'])
            ->orderBy('id')
            ->chunkById(200, function ($courses) use ($dryRun) {
                foreach ($courses as $course) {
                    $path = $course->thumbnail;
                    if (!$path || Str::startsWith($path, ['http://', 'https://'])) {
                        continue;
                    }
                    $converted = ImageService::convertToWebp($path, 'public', 85, true);
                    if ($converted !== $path) {
                        $this->line("Course #{$course->id}: {$path} -> {$converted}");
                        if (!$dryRun) {
                            Course::where('id', $course->id)->update(['thumbnail' => $converted]);
                        }
                    }
                }
            });

        // Transactions - proof (skip URL eksternal)
        $this->section('Transactions');
        Transaction::whereNotNull('proof')
            ->select(['id', 'proof'])
            ->orderBy('id')
            ->chunkById(200, function ($transactions) use ($dryRun) {
                foreach ($transactions as $trx) {
                    $path = $trx->proof;
                    if (!$path || Str::startsWith($path, ['http://', 'https://'])) {
                        continue;
                    }
                    $converted = ImageService::convertToWebp($path, 'public', 85, true);
                    if ($converted !== $path) {
                        $this->line("Transaction #{$trx->id}: {$path} -> {$converted}");
                        if (!$dryRun) {
                            Transaction::where('id', $trx->id)->update(['proof' => $converted]);
                        }
                    }
                }
            });

        // WebsiteSetting - logo & default_thumbnail (skip favicon karena kompatibilitas browser)
        $this->section('WebsiteSetting');
        $settings = WebsiteSetting::getInstance();
        if ($settings && $settings->exists) {
            $updates = [];
            if (!empty($settings->logo) && !Str::startsWith($settings->logo, ['http://', 'https://'])) {
                $converted = ImageService::convertToWebp($settings->logo, 'public', 85, true);
                if ($converted !== $settings->logo) {
                    $this->line("WebsiteSetting logo: {$settings->logo} -> {$converted}");
                    $updates['logo'] = $converted;
                }
            }
            if (!empty($settings->default_thumbnail) && !Str::startsWith($settings->default_thumbnail, ['http://', 'https://'])) {
                $converted = ImageService::convertToWebp($settings->default_thumbnail, 'public', 85, true);
                if ($converted !== $settings->default_thumbnail) {
                    $this->line("WebsiteSetting default_thumbnail: {$settings->default_thumbnail} -> {$converted}");
                    $updates['default_thumbnail'] = $converted;
                }
            }

            if (!empty($updates) && !$dryRun) {
                $settings->update($updates);
            }
        }

        $this->info('Migrasi konversi gambar selesai' . ($dryRun ? ' (DRY-RUN, tidak ada perubahan disimpan)' : ''));
        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info(str_repeat('-', 8) . " {$title} " . str_repeat('-', 8));
    }
}

