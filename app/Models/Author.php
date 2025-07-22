<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $guarded = false;

    public function books()
    {
        return $this->belongsToMany(Book::class)->withTimestamps();
    }
}
