<?php

use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // posts table
        $users = App\User::get();
        App\Post::truncate();
        $users->each(function ($user) {
            $user->posts()->save(factory(App\Post::class)->make());
            $user->posts()->save(factory(App\Post::class)->make());
            // 각 사용자가 2개의 Post를 가지고 있다.
        });
        $this->command->info('posts table seeded');

        $faker = Faker\Factory::create(); // 팩토리 패턴으로 create() 메서드 사용?
        $posts = App\Post::get();
        $tagIds = App\Tag::pluck('id')->toArray();

        // attach tags
        DB::table('post_tag')->truncate();
        foreach ($posts as $post) {
            $post->tags()->sync(
                $faker->randomElements($tagIds, rand(1, 2))
            );
        }
        $this->command->info('tags pivot table seeded');
    }
}
