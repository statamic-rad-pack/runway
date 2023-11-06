<?php

namespace DoubleThreeDigital\Runway\Tests\Fixtures\Database\Factories;

use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }

    public function withPosts(int $count = 1): Factory
    {
        return $this->afterCreating(function (Author $author) use ($count) {
            $author->posts()->createMany(
                PostFactory::new()->count($count)->make()->toArray()
            );
        });
    }
}
