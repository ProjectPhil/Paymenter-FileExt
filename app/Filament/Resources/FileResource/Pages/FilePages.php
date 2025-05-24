<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;

class ListFiles extends ListRecords {
    protected static string $resource = FileResource::class;
}

class CreateFile extends CreateRecord {
    protected static string $resource = FileResource::class;
}

class EditFile extends EditRecord {
    protected static string $resource = FileResource::class;
} 