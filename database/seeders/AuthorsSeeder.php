<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;

class AuthorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $categories = Category::factory()->count(3)->create();

        Author::factory()
        ->has(
            Post::factory()
            ->has(
                Comment::factory()
                ->count(5)
            )
            ->hasAttached($categories)
            ->count(3)
        )
        ->count(3)
        ->create();
    }
}
