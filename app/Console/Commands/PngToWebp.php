<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class PngToWebp extends Command
{
    protected $signature = 'pngto:web';
    protected $description = 'Convert PNG files (including misnamed JPEGs) to WebP';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $folderPath = storage_path('app/public/workflow-task-uploads');
        $prefixesToDelete = ['SIGN-202509'];
        $files = File::files($folderPath);

        foreach ($files as $file) {
            $fileName = $file->getFilename();

            foreach ($prefixesToDelete as $prefix) {
                if (str_starts_with($fileName, $prefix) && str_ends_with(strtolower($fileName), '.png')) {
                    $xPath = $file->getRealPath();

                    if (!file_exists($xPath)) {
                        $this->error('File not found at path: ' . $xPath);
                        continue;
                    }

                    $fileContents = file_get_contents($xPath);
                    $image = @imagecreatefromstring($fileContents);

                    if ($image === false) {
                        $this->error('Failed to create image from: ' . $xPath);
                        continue;
                    }

                    $fileNameWithoutExt = pathinfo($xPath, PATHINFO_FILENAME);
                    $convertedDir = storage_path('app/public/workflow-task-uploads');
                    $webpPath = $convertedDir . '/' . $fileNameWithoutExt . '.webp';

                    if (!file_exists($convertedDir)) {
                        mkdir($convertedDir, 0755, true);
                    }

                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);

                    if (imagewebp($image, $webpPath, 70)) {
                        $this->info('Saved WebP at: ' . $webpPath);
                        if (file_exists($webpPath) && is_file($webpPath)) {
                            File::delete($xPath);
                            $this->info('Deleted original at: ' . $xPath);
                        }
                    } else {
                        $this->error('Failed to convert to WebP: ' . $xPath);
                    }

                    imagedestroy($image);
                    break;
                }
            }
        }
    }
}