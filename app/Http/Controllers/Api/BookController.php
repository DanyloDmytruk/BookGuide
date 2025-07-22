<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return BookResource::collection(Book::with('authors')->paginate(15));
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

        $book = Book::create($data);
        $book->authors()->sync($data['author_ids']);

        return new BookResource($book->load('authors'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new BookResource($book->load('authors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($book->image) {
                Storage::disk('public')->delete($book->image);
            }

            $filename = Str::uuid() . '.' . $request->image->extension();
            $data['image'] = $request->image->storeAs('books', $filename, 'public');
        }

        $book->update($data);
        $book->authors()->sync($data['author_ids']);

        return new BookResource($book->load('authors'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($book->image) {
            Storage::disk('public')->delete($book->image);
        }

        $book->delete();

        return response()->noContent();
    }
}
