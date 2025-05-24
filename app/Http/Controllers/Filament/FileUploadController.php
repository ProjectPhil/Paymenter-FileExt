<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller {
    public function __invoke(Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240',
            'disk' => 'required|string',
            'directory' => 'required|string',
        ]);

        $file = $request->file('file');
        $disk = $request->input('disk');
        $directory = $request->input('directory');

        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, $disk);

        return response()->json(['path' => $path]);
    }
} 