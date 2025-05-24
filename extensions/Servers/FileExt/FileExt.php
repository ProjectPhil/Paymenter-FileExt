<?php

namespace Paymenter\Extensions\Servers\FileExt;

use App\Classes\Extension\Server;
use App\Models\Service;
use App\Models\DownloadFile;
use App\Traits\HasFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Serializable;
use App\Jobs\ProcessFileUpload;

class FileExt extends Server implements Serializable
{
    use HasFileUpload;

    public $config = [];

    public function __construct(array $config = [])
    {
        // Process any file uploads immediately
        if (isset($config['download_file']) && is_object($config['download_file'])) {
            unset($config['download_file']);
        }
        $this->config = $config;
    }

    public function serialize(): string
    {
        $config = $this->config;
        
        // Remove any file objects from the config
        if (isset($config['download_file'])) {
            if (is_object($config['download_file'])) {
                unset($config['download_file']);
            } elseif (is_array($config['download_file'])) {
                $config['download_file'] = $config['download_file']['path'] ?? null;
            }
        }
        
        return serialize([
            'config' => $config,
        ]);
    }

    public function unserialize(string $data): void
    {
        $data = unserialize($data);
        $this->config = $data['config'] ?? [];
    }

    /**
     * Get all the configuration for the extension
     * 
     * @param array $values
     * @return array
     */
    public function getConfig($values = []): array
    {
        return [
            'download_path' => [
                'type' => 'text',
                'name' => 'download_path',
                'friendlyName' => 'Download Path',
                'description' => 'The path where files will be stored for download',
                'required' => true,
                'default' => '/var/www/files'
            ],
            'allowed_extensions' => [
                'type' => 'text',
                'name' => 'allowed_extensions',
                'friendlyName' => 'Allowed File Extensions',
                'description' => 'Comma-separated list of allowed file extensions (e.g., pdf,zip,doc)',
                'required' => true,
                'default' => 'pdf,zip,doc'
            ]
        ];
    }

    /**
     * Get product config
     * 
     * @param array $values
     * @return array
     */
    public function getProductConfig($values = []): array
    {
        return [
            [
                'type' => 'file-upload',
                'name' => 'download_file',
                'friendlyName' => 'Download File',
                'description' => 'File that will be available for download',
                'required' => false,
                'disk' => 'public',
                'directory' => 'downloads',
                'acceptedFileTypes' => $this->getAcceptedFileTypes(),
                'maxSize' => 10240
            ],
            [
                'type' => 'select',
                'name' => 'max_downloads',
                'friendlyName' => 'Maximum Downloads',
                'description' => 'Maximum number of times a file can be downloaded',
                'required' => true,
                'default' => 1,
                'options' => [
                    1 => '1 download',
                    2 => '2 downloads',
                    3 => '3 downloads',
                    5 => '5 downloads',
                    10 => '10 downloads',
                    20 => '20 downloads',
                    50 => '50 downloads',
                    100 => '100 downloads',
                    -1 => 'Unlimited downloads'
                ]
            ],
            [
                'type' => 'datetime',
                'name' => 'download_expiry',
                'friendlyName' => 'Download Expiry',
                'description' => 'Date and time the download link expires',
                'required' => false,
            ]
        ];
    }

    /**
    * Check if current configuration is valid
    *
    * @return bool|string
    */
    public function testConfig(): bool|string
    {
        $config = $this->getConfig();
        if (!isset($config['download_path']) || empty($config['download_path'])) {
            return 'Download path is required';
        }
        return true;
    }

    /**
     * Create a server 
     * 
     * @param Service $service
     * @param array $settings (product settings)
     * @param array $properties (checkout options)
     * @return bool
     */
    public function createServer(Service $service, $settings, $properties)
    {
        // Create download directory if it doesn't exist
        $downloadPath = $settings['download_path'] ?? 'downloads';
        if (!Storage::exists($downloadPath)) {
            Storage::makeDirectory($downloadPath);
        }
        
        // Handle file upload if present
        if (isset($settings['download_file'])) {
            $fileInfo = $this->handleFileUpload($settings['download_file'], $downloadPath, $service->id, $settings);
            if ($fileInfo) {
                $settings['download_file'] = $fileInfo['path'];
            } else {
                unset($settings['download_file']);
            }
        }

        // Update the config with processed settings
        $this->config = array_merge($this->config, $settings);

        return true;
    }

    /**
     * Suspend a server
     * 
     * @param Service $service
     * @param array $settings (product settings)
     * @param array $properties (checkout options)
     * @return bool
     */
    public function suspendServer(Service $service, $settings, $properties)
    {
        return false;
    }

    /**
     * Unsuspend a server
     * 
     * @param Service $service
     * @param array $settings (product settings)
     * @param array $properties (checkout options)
     * @return bool
     */
    public function unsuspendServer(Service $service, $settings, $properties)
    {
        return false;
    }

    /**
     * Terminate a server
     * 
     * @param Service $service
     * @param array $settings (product settings)
     * @param array $properties (checkout options)
     * @return bool
     */
    public function terminateServer(Service $service, $settings, $properties)
    {
        return false;
    }

    /**
     * Upload a file for download
     * 
     * @param Service $service
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|false Returns the file path on success, false on failure
     */
    public function uploadFile(Service $service, UploadedFile $file): string|false
    {
        try {
            $settings = $service->product->settings;
            $downloadPath = $settings['download_path'] ?? 'downloads';
            
            // Check file extension
            $allowedExtensions = explode(',', $settings['allowed_extensions'] ?? 'pdf,zip,doc,docx');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return false;
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $extension;
            
            // Store the file
            if (Storage::putFileAs($downloadPath, $file, $filename)) {
                // Create download file record
                DownloadFile::create([
                    'service_id' => $service->id,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'max_downloads' => $settings['max_downloads'] ?? 1,
                    'expires_at' => isset($settings['download_expiry']) ? now()->parse($settings['download_expiry']) : now()->addDays(7),
                ]);
                
                // Return the file path
                return $downloadPath . '/' . $filename;
            }
        } catch (\Exception $e) {
            // Log the error if needed
            return false;
        }

        return false;
    }

    /**
     * Get download URL for a file
     * 
     * @param Service $service
     * @param string $filename
     * @return string|null
     */
    public function getDownloadUrl(Service $service, string $filename): ?string
    {
        $downloadFile = DownloadFile::where('service_id', $service->id)
            ->where('filename', $filename)
            ->first();

        if (!$downloadFile) {
            return null;
        }

        // Check if file has expired
        if ($downloadFile->expires_at && $downloadFile->expires_at->isPast()) {
            return null;
        }

        // Check if max downloads reached
        if ($downloadFile->download_count >= $downloadFile->max_downloads) {
            return null;
        }

        $settings = $service->product->settings;
        $downloadPath = $settings['download_path'] ?? 'downloads';
        
        if (!Storage::exists($downloadPath . '/' . $filename)) {
            return null;
        }

        // Increment download count
        $downloadFile->increment('download_count');

        // Generate temporary signed URL
        return Storage::temporaryUrl(
            $downloadPath . '/' . $filename,
            now()->addMinutes(5)
        );
    }

    /**
     * Get all files for a service
     * 
     * @param Service $service
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getServiceFiles(Service $service)
    {
        return DownloadFile::where('service_id', $service->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereRaw('download_count < max_downloads')
            ->get();
    }
}