<?php

namespace DoubleThreeDigital\Runway\Tests\AugmentedModelTest;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Spatie\TestTime\TestTime;

class AugmentedModelTest extends TestCase
{
    /** @test */
    public function it_gets_values()
    {
        TestTime::freeze('Y-m-d H:i:s', '2020-01-01 13:46:12');

        $author = $this->authorFactory(1, [
            'name' => 'John Doe',
        ]);

        $post = $this->postFactory(1, [
            'title' => 'My First Post',
            'slug' => 'my-first-post',
            'body' => 'Blah blah blah...',
            'author_id' => $author->id,
        ]);

        $post->refresh();

        $augmented = new AugmentedModel($post);

        $this->assertEquals('My First Post', $augmented->get('title')->value());
        $this->assertEquals('my-first-post', $augmented->get('slug')->value());
        $this->assertEquals('Blah blah blah...', $augmented->get('body')->value());
        $this->assertEquals('2020-01-01 13:46:12', $augmented->get('created_at')->value()->format('Y-m-d H:i:s'));
        $this->assertEquals('/posts/my-first-post', $augmented->get('url')->value());

        $this->assertIsArray($augmented->get('author_id')->value());
        $this->assertEquals($author->id, $augmented->get('author_id')->value()['id']->value());
        $this->assertEquals('John Doe', $augmented->get('author_id')->value()['name']->value());
    }

    /** @test */
    public function it_gets_nested_values()
    {
        $post = $this->postFactory(1, [
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $augmented = new AugmentedModel($post);

        $this->assertIsArray($augmented->get('values')->value());

        $this->assertEquals('Alternative Title...', $augmented->get('values')->value()['alt_title']->value());
        $this->assertEquals('<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>', trim($augmented->get('values')->value()['alt_body']->value()));
    }
}
