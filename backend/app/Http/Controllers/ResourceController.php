<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function storeBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'year' => 'required|integer',
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Genera slug univoco
        $slug = $this->generateUniqueSlug(Str::slug($request->title));

        // Salva il file su disco
        $filePath = $file->store('resources', 'public');

        $resource = new Resource();
        $resource->type = 'book';
        $resource->title = $request->title;
        $resource->slug = $slug;
        $resource->author = $request->author;
        $resource->year = $request->year;
        $resource->file_path = $filePath;
        $resource->file_name = $originalName;
        $resource->mime_type = $file->getMimeType();
        $resource->save();

        return response()->json([
            'message' => 'Book saved successfully',
            'slug' => $slug
        ], 201);
    }

    private function generateUniqueSlug($slug)
    {
        $original = $slug;
        $count = 1;

        while (Resource::where('slug', $slug)->exists()) {
            $slug = "{$original}-" . $count++;
        }

        return $slug;
    }

    public function getResourceFile($slug)
    {
        $resource = Resource::where('slug', $slug)->firstOrFail();

        $filePath = storage_path('app/public/' . $resource->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => $resource->mime_type,
            'Content-Disposition' => 'inline; filename="' . $resource->file_name . '"'
        ]);
    }

    public function getAllResources()
    {
        $books = Resource::where('type', 'book')->get();
        $guides = Resource::where('type', 'guide')->get();
        $faqs = Resource::where('type', 'faq')->get();

        return response()->json([
            'books' => $books,
            'guides' => $guides,
            'faqs' => $faqs
        ]);
    }
}
