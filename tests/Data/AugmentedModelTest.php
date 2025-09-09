<?php

namespace StatamicRadPack\Runway\Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class AugmentedModelTest extends TestCase
{
    #[Test]
    public function it_gets_values()
    {
        TestTime::freeze('Y-m-d H:i:s', '2020-01-01 13:46:12');

        $author = Author::factory()->create(['name' => 'John Doe']);

        $post = Post::factory()->create([
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

        $this->assertIsArray($augmented->get('author')->value());
        $this->assertEquals($author->id, $augmented->get('author')->value()['id']->value());
        $this->assertEquals('John Doe', $augmented->get('author')->value()['name']->value());

        $this->assertIsArray($augmented->get('author_id')->value());
        $this->assertEquals($author->id, $augmented->get('author_id')->value()['id']->value());
        $this->assertEquals('John Doe', $augmented->get('author_id')->value()['name']->value());
    }

    #[Test]
    public function it_gets_supplemental_values()
    {
        $post = Post::factory()->create([
            'title' => 'My First Post',
            'body' => 'Blah blah blah...',
        ]);

        $post->setSupplement('title', 'My Supplemental Title');

        $augmented = new AugmentedModel($post);

        $this->assertEquals('My Supplemental Title', $augmented->get('title')->value()); // Supplemental value
        $this->assertEquals('Blah blah blah...', $augmented->get('body')->value()); // Non-supplemental value
    }

    #[Test]
    public function it_gets_nested_values_via_nested_field_prefix()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $augmented = new AugmentedModel($post);

        $this->assertIsArray($augmented->get('values')->value());

        // Eg. values:alt_title, values:alt_body
        $this->assertEquals('Alternative Title...', $augmented->get('values')->value()['alt_title']->value());
        $this->assertEquals('<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>', trim($augmented->get('values')->value()['alt_body']->value()));
    }

    #[Test]
    public function it_gets_nested_supplemental_values_via_nested_field_prefix()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $post->setSupplement('values_alt_title', 'Supplemental Title...');

        $augmented = new AugmentedModel($post);

        $this->assertIsArray($augmented->get('values')->value());

        // Eg. values:alt_title, values:alt_body
        $this->assertEquals('Supplemental Title...', $augmented->get('values')->value()['alt_title']->value());
        $this->assertEquals('<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>', trim($augmented->get('values')->value()['alt_body']->value()));
    }

    #[Test]
    public function it_gets_nested_values_via_nested_field_handle()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $augmented = new AugmentedModel($post);

        $this->assertIsArray($augmented->get('values')->value());

        // Eg. values_alt_title, values_alt_body
        $this->assertEquals('Alternative Title...', $augmented->get('values_alt_title')->value());
        $this->assertEquals('<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>', trim($augmented->get('values_alt_body')->value()));
    }

    #[Test]
    public function it_gets_nested_supplemental_values_via_nested_field_handle()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $post->setSupplement('values_alt_title', 'Supplemental Title...');

        $augmented = new AugmentedModel($post);

        $this->assertIsArray($augmented->get('values')->value());

        // Eg. values_alt_title, values_alt_body
        $this->assertEquals('Supplemental Title...', $augmented->get('values_alt_title')->value());
        $this->assertEquals('<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>', trim($augmented->get('values_alt_body')->value()));
    }

    #[Test]
    public function it_gets_value_from_model_accessor()
    {
        $post = Post::factory()->create();

        $augmented = new AugmentedModel($post);

        $this->assertEquals('This is an excerpt.', $augmented->get('excerpt')->value());
    }

    #[Test]
    public function it_gets_value_from_appended_attribute()
    {
        $post = Post::factory()->create();

        $augmented = new AugmentedModel($post);

        $this->assertEquals('This is an appended value.', $augmented->get('appended_value')->value());
    }

    #[Test]
    public function it_gets_value_from_mutator()
    {
        $post = Post::factory()->create(['mutated_value' => 'Foo']);

        $augmented = new AugmentedModel($post);

        $this->assertEquals('Foo is a mutated value.', $augmented->get('mutated_value')->value());
    }
}
