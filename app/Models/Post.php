<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [ "title","body","author_id"];

    public function author(){
        return $this->belongsTo(Author::class);//->select(['id','name']);
    }

    public function comments(){
        return $this->hasMany(Comment::class);//->select(['id', 'body']);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'posts_categories_pivot','post_id','category_id');
    }
}
