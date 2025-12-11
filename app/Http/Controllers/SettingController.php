<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'required',
        ]);

        Setting::updateOrCreate(
            ['id' => 1],
            [
                'maintenance_mode' => $request->maintenance_mode ? 1 : 0,
            ]
        );

        return back()->with('success', 'Settings updated successfully.');
    }

    public function addWatermark(Request $request) {
        $imagePath = storage_path("app/public/workflow-task-uploads/SIGN-2025101312533168eca8f3d3cd2.png");

        if (file_exists($imagePath) && is_file($imagePath)) {
            try {

            $img = \Image::make($imagePath);

            $img->text(rand(1, 100000000000000000), $img->width() - 10, 10, function ($font) {
                $font->file(storage_path('fonts/Roboto-Regular.ttf'));
                $font->size(45);
                $font->color('#ffffff');
                $font->align('right');
                $font->valign('top');
            });

            $path = $imagePath;
            $filename = !empty($path) ? basename($path) : null;

            if ($filename) {
                $img->save("storage/workflow-task-uploads/{$filename}", 90);
            }                
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    public function givePermission() {
        exec("sudo chown -R teapost-one:teapost-one /home/teapost-one/htdocs/one.teapost.in/storage");
        echo "Permission given successfully";
    }
}
