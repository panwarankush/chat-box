<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index()
    {
        // return DB::table('posts')->get();
        // $post =  Post::with('author:id,name')
        // ->with('comments')
        // ->with('categories:id,name')
        // ->select('id','title','body','author_id')
        // ->get();
        $post =  Post::with(
            // 'comments:post_id,body' ,
            'author:id,name',
            // 'categories:id,name',
        )
        ->select('id','title','body','author_id')
        ->whereHas('author', function ($query) {
            $query->where('name','Candice Howell');
        })
        ->get();
        return $post;
    }
}
