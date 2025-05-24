<?php

namespace App\Filament\Resources\ProductFileResource\Pages;

use App\Filament\Resources\ProductFileResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;

class ListProductFiles extends ListRecords {
    protected static string $resource = ProductFileResource::class;
}

class CreateProductFile extends CreateRecord {
    protected static string $resource = ProductFileResource::class;
}

class EditProductFile extends EditRecord {
    protected static string $resource = ProductFileResource::class;
} 