<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_url',
        'author',
        'isbn',
        'date',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'book_id',
            'user_id'
        )->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
