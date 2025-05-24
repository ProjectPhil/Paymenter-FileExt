<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Models\DownloadFile;
use App\Traits\HasFileUpload;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class FileResource extends Resource
{
    use HasFileUpload;

    protected static ?string $model = DownloadFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-download';

    protected static ?string $navigationGroup = 'Files';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->required(),
                Forms\Components\FileUpload::make('file')
                    ->label('File')
                    ->required()
                    ->acceptedFileTypes((new static)->getAcceptedFileTypes())
                    ->maxSize(10240)
                    ->directory('downloads')
                    ->preserveFilenames()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            if (is_string($state)) {
                                $set('original_name', basename($state));
                                $set('filename', basename($state));
                                $set('file_size', Storage::size($state));
                            }
                        }
                    }),
                Forms\Components\TextInput::make('original_name')
                    ->required()
                    ->hidden(),
                Forms\Components\TextInput::make('filename')
                    ->required()
                    ->hidden(),
                Forms\Components\TextInput::make('file_size')
                    ->required()
                    ->hidden(),
                Forms\Components\TextInput::make('max_downloads')
                    ->label('Maximum Downloads')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_downloads')
                    ->label('Max Downloads')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\FilePages\ListFiles::route('/'),
            'create' => Pages\FilePages\CreateFile::route('/create'),
            'edit' => Pages\FilePages\EditFile::route('/{record}/edit'),
        ];
    }
} 