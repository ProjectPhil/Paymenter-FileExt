<?php

namespace App\Classes;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FilamentInput
{
    public static function convert($setting)
    {
        // Handle array of settings
        if (is_array($setting) && isset($setting[0])) {
            $inputs = [];
            foreach ($setting as $s) {
                $inputs[] = self::convert($s);
            }
            return $inputs;
        }

        // Convert array to object if needed
        if (is_array($setting)) {
            $setting = (object) $setting;
        }

        // Ensure required properties exist
        if (!isset($setting->name)) {
            throw new \Exception("Setting must have a 'name' property");
        }

        // Set default friendlyName if not provided
        if (!isset($setting->friendlyName)) {
            $setting->friendlyName = ucfirst(str_replace(['_', '.'], ' ', $setting->name));
        }

        // Set default type if not provided
        if (!isset($setting->type)) {
            $setting->type = 'text';
        }

        // Handle array type (for select options defined as an array)
        if (is_array($setting->type)) {
            $setting->type = 'select';
            $setting->options = $setting->type;
        }

        switch ($setting->type) {
            case 'text':
                $input = TextInput::make($setting->name)
                    ->label($setting->friendlyName)
                    ->required($setting->required ?? false)
                    ->hint($setting->hint ?? '')
                    ->hintColor('primary');
                break;

            case 'password':
                $input = TextInput::make($setting->name)
                    ->label($setting->friendlyName)
                    ->password()
                    ->required($setting->required ?? false)
                    ->hint($setting->hint ?? '')
                    ->hintColor('primary');
                break;

            case 'select':
                $input = Select::make($setting->name)
                    ->label($setting->friendlyName)
                    ->options($setting->options ?? [])
                    ->required($setting->required ?? false)
                    ->hint($setting->hint ?? '')
                    ->hintColor('primary');
                break;

            case 'repeater':
                if (!isset($setting->fields)) {
                    throw new \Exception("Repeater type must have 'fields' property");
                }

                $input = Repeater::make($setting->name)
                    ->label($setting->friendlyName)
                    ->schema(function () use ($setting) {
                        $fields = [];
                        foreach ($setting->fields as $field) {
                            // Convert array field to object if needed
                            if (is_array($field)) {
                                $field = (object) $field;
                            }

                            // Set default friendlyName for field if not provided
                            if (!isset($field->friendlyName)) {
                                $field->friendlyName = ucfirst(str_replace(['_', '.'], ' ', $field->name));
                            }

                            // Set default type for field if not provided
                            if (!isset($field->type)) {
                                $field->type = 'text';
                            }

                            // Handle array type for fields (for select options defined as an array)
                            if (is_array($field->type)) {
                                $field->type = 'select';
                                $field->options = $field->type;
                            }

                            // Log the field data before the switch
                            Log::info('FilamentInput Repeater Field Data:', (array) $field);

                             switch ($field->type) {
                                case 'text':
                                    $fields[] = TextInput::make($field->name)
                                        ->label($field->friendlyName)
                                        ->required($field->required ?? false)
                                        ->hint($field->description ?? '');
                                    break;
                                case 'password':
                                     $fields[] = TextInput::make($field->name)
                                        ->label($field->friendlyName)
                                        ->password()
                                        ->required($field->required ?? false)
                                        ->hint($field->description ?? '');
                                    break;
                                case 'select':
                                    $fields[] = Select::make($field->name)
                                        ->label($field->friendlyName)
                                        ->options($field->options ?? [])
                                        ->required($field->required ?? false)
                                        ->hint($field->description ?? '');
                                    break;
                                case 'file-upload':
                                     $fields[] = FileUpload::make($field->name)
                                        ->label($field->friendlyName)
                                        ->acceptedFileTypes($field->acceptedFileTypes ?? [])
                                        ->maxSize($field->maxSize ?? null)
                                        ->disk($field->disk ?? 'public')
                                        ->directory($field->directory ?? '')
                                        ->required($field->required ?? false)
                                        ->hint($field->description ?? '');
                                     break;
                                 case 'datetime':
                                     $fields[] = DateTimePicker::make($field->name)
                                         ->label($field->friendlyName)
                                         ->required($field->required ?? false)
                                         ->hint($field->description ?? '');
                                     break;
                                default:
                                    throw new \Exception("Unknown field type: {$field->type}");
                            }
                        }
                        return $fields;
                    })
                    ->required($setting->required ?? false)
                    ->hint($setting->description ?? '')
                    ->hintColor('primary');
                break;

            case 'file-upload':
                 $input = FileUpload::make($setting->name)
                    ->label($setting->friendlyName)
                    ->acceptedFileTypes($setting->acceptedFileTypes ?? [])
                    ->maxSize($setting->maxSize ?? null)
                    ->disk($setting->disk ?? 'public')
                    ->directory($setting->directory ?? '')
                    ->required($setting->required ?? false)
                    ->hint($setting->hint ?? '')
                    ->hintColor('primary');
                 break;
            case 'datetime':
                 $input = DateTimePicker::make($setting->name)
                     ->label($setting->friendlyName)
                     ->required($setting->required ?? false)
                     ->hint($setting->hint ?? '')
                     ->hintColor('primary');
                 break;

            default:
                throw new \Exception("Unknown input type: {$setting->type}");
        }

        return $input;
    }
}