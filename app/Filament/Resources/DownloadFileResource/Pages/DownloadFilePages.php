<?php

namespace App\Filament\Resources\DownloadFileResource\Pages;

use App\Filament\Resources\DownloadFileResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;

class ListDownloadFiles extends ListRecords {
    protected static string $resource = DownloadFileResource::class;
}

class CreateDownloadFile extends CreateRecord {
    protected static string $resource = DownloadFileResource::class;
}

class EditDownloadFile extends EditRecord {
    protected static string $resource = DownloadFileResource::class;
} 