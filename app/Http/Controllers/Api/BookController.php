<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with('authors');

        if ($title = $request->input('title')) {
            $query->where('title', 'like', "%$title%");
        }

        if ($author = $request->input('author')) {
            $query->whereHas('authors', function ($q) use ($author) {
                $q->where('first_name', 'like', "%$author%")
                    ->orWhere('last_name', 'like', "%$author%");
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhereHas('authors', function ($authorQuery) use ($search) {
                        $authorQuery->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                    });
            });
        }

        $query->orderBy('title');

        return BookResource::collection($query->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $filename = Str::uuid() . '.' . $request->image->extension();
            $path = $request->image->storeAs('books', $filename, 'public');
            $data['image'] = $path;
        }

        $book = Book::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'publication_date' => $data['publication_date'] ?? null,
        ]);

        // Sync authors in pivot table
        $book->authors()->sync($data['author_ids']);

        return new BookResource($book->load('authors'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return new BookResource($book->load('authors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBookRequest $request, Book $book)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($book->image) {
                Storage::disk('public')->delete($book->image);
            }

            $filename = Str::uuid() . '.' . $request->image->extension();
            $data['image'] = $request->image->storeAs('books', $filename, 'public');
        }

        $book->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? $book->image,
            'publication_date' => $data['publication_date'] ?? null,
        ]);
        $book->authors()->sync($data['author_ids']);

        return new BookResource($book->load('authors'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if ($book->image) {
            Storage::disk('public')->delete($book->image);
        }

        $book->delete();

        return response()->noContent();
    }
}
