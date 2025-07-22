<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = false;

    public function authors()
    {
        return $this->belongsToMany(Author::class)->withTimestamps();
    }
}
