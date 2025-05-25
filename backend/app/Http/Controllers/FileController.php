<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;


class FileController extends Controller
{
    public function index()
    {
                /** @var User $user */

        return auth()->user()->files;
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        
        /** @var User $user */
        $user = Auth::user();
        $file = $request->file('file');
        
        $path = $file->store('user_files/'.$user->id, 'local');

        return $user->files()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);
    }

    public function download(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function destroy(File $file)
    {
        Storage::delete($file->path);
        $file->delete();
        return response()->noContent();
    }

   public function show(File $file)
{
    if ($file->user_id !== auth()->id()) {
        abort(403, 'Non autorizzato ad accedere a questo file');
    }

    $path = Storage::disk('local')->path($file->path);

    return response()->file($path, [
        'Content-Type' => $file->mime_type,
        'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ]);
}
}
