<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;

class FileUploadField extends Field
{
    protected string $view = 'filament.forms.components.file-upload-field';

    protected string $disk = 'public';

    protected string $directory = 'downloads';

    protected array $acceptedFileTypes = [];

    protected int $maxSize = 10240;

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;
        return $this;
    }

    public function maxSize(int $size): static
    {
        $this->maxSize = $size;
        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getAcceptedFileTypes(): array
    {
        return $this->acceptedFileTypes;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function dehydrateStateUsing(callable $callback): static
    {
        $this->dehydrateStateUsing = function ($state) {
            if (is_object($state) && method_exists($state, 'getRealPath')) {
                // Get the real path of the temporary file
                $realPath = $state->getRealPath();
                if ($realPath && file_exists($realPath)) {
                    // Get the original filename and extension
                    $originalName = $state->getClientOriginalName();
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    
                    // Generate a unique filename
                    $filename = uniqid() . '.' . $extension;
                    $path = $this->directory . '/' . $filename;
                    
                    // Read the file content and store it
                    $content = file_get_contents($realPath);
                    if ($content !== false) {
                        Storage::disk($this->disk)->put($path, $content);
                        return $path;
                    }
                }
            }
            return $state;
        };
        return $this;
    }
} 