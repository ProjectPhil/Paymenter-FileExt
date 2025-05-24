<?php

namespace App\Jobs;

use App\Models\DownloadFile;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $realPath;
    protected $serviceId;
    protected $settings;

    public function __construct(string $realPath, int $serviceId, array $settings)
    {
        $this->realPath = $realPath;
        $this->serviceId = $serviceId;
        $this->settings = $settings;
    }

    public function handle()
    {
        if (!file_exists($this->realPath)) {
            return;
        }

        $downloadPath = $this->settings['download_path'] ?? 'downloads';
        $originalName = basename($this->realPath);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filePath = $downloadPath . '/' . $filename;

        // Read and store the file
        $content = file_get_contents($this->realPath);
        if ($content !== false) {
            Storage::put($filePath, $content);

            // Create download file record
            DownloadFile::create([
                'service_id' => $this->serviceId,
                'filename' => $filename,
                'original_name' => $originalName,
                'file_size' => strlen($content),
                'max_downloads' => $this->settings['max_downloads'] ?? 1,
                'expires_at' => isset($this->settings['download_expiry']) 
                    ? now()->parse($this->settings['download_expiry']) 
                    : now()->addDays(7),
            ]);

            // Update service settings with the file path
            $service = Service::find($this->serviceId);
            if ($service) {
                $settings = $service->settings;
                $settings['download_file'] = $filePath;
                $service->settings = $settings;
                $service->save();
            }
        }
    }
} 