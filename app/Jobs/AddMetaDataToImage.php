<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddMetaDataToImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;    

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dateTime = isset($this->data['timestamp']) ? $this->data['timestamp']: '';
        $latLong  = 'Lat: ' . ($this->data['latitude']) . ', Long: ' . $this->data['longitude'];
        $text     = "$dateTime\n$latLong";
        $imagePath = isset($this->data['path']) ? $this->data['path'] : '';

        $path = $imagePath;
        $filename = !empty($path) ? basename($path) : null;

        if (!empty($dateTime) && $dateTime != 2 && !empty($this->data['latitude']) && !empty($this->data['longitude'])) {
            \App\Models\ImageTimeStampPrinted::updateOrCreate([
                'task_id' => $this->data['task_id'],
                'field_name' => $this->data['field_name'],
                'file' => $path
            ],[
                'task_id' => $this->data['task_id'],
                'field_name' => $this->data['field_name'],
                'timestamp' => $dateTime,
                'latitude' => isset($this->data['latitude']) ? $this->data['latitude']: '',
                'longitude' => isset($this->data['longitude']) ? $this->data['longitude']: '',
                'file' => $path
            ]);
        }
    }
}