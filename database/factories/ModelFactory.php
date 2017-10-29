<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Post::class, function (Faker\Generator $faker) {

    return [
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
    ];
});

$factory->define(App\Comment::class, function (Faker\Generator $faker) {
    $userIds = App\User::pluck('id')->toArray(); // User에서 id만 뽑아서 array로 만든다.

    return [
        'user_id' => $faker->randomElement($userIds),
        'content' => $faker->paragraph,
    ];
});