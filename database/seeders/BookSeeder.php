<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Factories\AuthorFactory;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = Author::all();

        if ($authors->count() < 5) {
            $authors = Author::factory()->count(10)->create();
        }

        Book::factory()
            ->count(30)
            ->create()
            ->each(function ($book) use ($authors) {
                $book->authors()->attach($authors->random(rand(1, 3))->pluck('id')->toArray());
            });
    }
}
