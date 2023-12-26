<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Database\Factories;

use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $title = implode(' ', $this->faker->words(6)),
            'slug' => str_slug($title),
            'body' => implode(' ', $this->faker->paragraphs(10)),
            'author_id' => Author::factory()->create()->id,
        ];
    }
}
