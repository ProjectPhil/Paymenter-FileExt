<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessFileUpload;

trait HasFileUpload
{
    protected function handleFileUpload($file, $directory = 'downloads', $serviceId = null, $settings = [])
    {
        if (!$file) {
            return null;
        }

        if (is_object($file) && method_exists($file, 'getRealPath')) {
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath)) {
                if ($serviceId) {
                    // If we have a service ID, dispatch a job
                    ProcessFileUpload::dispatch($realPath, $serviceId, $settings);
                    return null;
                } else {
                    // Otherwise process immediately
                    $content = file_get_contents($realPath);
                    if ($content !== false) {
                        $filename = Str::random(40) . '.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                        $path = $directory . '/' . $filename;
                        Storage::put($path, $content);
                        return [
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'filename' => $filename,
                            'file_size' => strlen($content)
                        ];
                    }
                }
            }
        } elseif (is_string($file)) {
            return [
                'path' => $file,
                'original_name' => basename($file),
                'filename' => basename($file),
                'file_size' => Storage::size($file)
            ];
        }

        return null;
    }

    protected function getAcceptedFileTypes(): array
    {
        return [
            'application/pdf',
            'application/zip',
            'application/x-zip',
            'application/x-zip-compressed',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word',
            '.pdf',
            '.zip',
            '.doc',
            '.docx'
        ];
    }
} 