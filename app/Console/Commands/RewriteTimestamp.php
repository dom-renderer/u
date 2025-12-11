<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImageTimeStampPrinted;
use Intervention\Image\Facades\Image;

class RewriteTimestamp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewrite:mark';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry adding timestamp and coordinates directly to images that were not printed.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $items = ImageTimeStampPrinted::where('printed', 0)->get();

        if ($items->isEmpty()) {
            $this->info('✅ All images are already printed.');
            return 0;
        }

        $this->info("🖼 Found {$items->count()} unprinted image(s). Retrying...");

        foreach ($items as $item) {
            $imagePath = $item->file;
            $dateTime  = $item->timestamp ?? '';
            $latLong   = 'Lat: ' . ($item->latitude ?? '') . ', Long: ' . ($item->longitude ?? '');
            $text      = "$dateTime\n$latLong";

            if (!file_exists($imagePath) || !is_file($imagePath)) {
                $this->error("❌ Image not found: {$imagePath}");
                continue;
            }

            try {
                $img = Image::make($imagePath);

                $img->text($text, $img->width() - 10, 10, function ($font) {
                    $font->file(storage_path('fonts/Roboto-Regular.ttf'));
                    $font->size(45);
                    $font->color('#ffffff');
                    $font->align('right');
                    $font->valign('top');
                });

                $img->save($imagePath, 90);

                $item->update(['printed' => true]);

                $this->info("✅ Printed on image: {$imagePath}");
            } catch (\Exception $e) {
                $this->error("❌ Failed: {$imagePath} ({$e->getMessage()})");
            }
        }

        $this->info('🎯 All unprinted images have been processed.');
        return 0;
    }
}