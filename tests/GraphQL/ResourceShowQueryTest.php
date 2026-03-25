<?php

namespace StatamicRadPack\Runway\Tests\GraphQL;

use Facades\Statamic\CP\LivePreview;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourceShowQueryTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('runway.resources.'.Post::class.'.graphql', true);
    }

    #[Test]
    public function it_returns_published_model()
    {
        $post = Post::factory()->create(['title' => 'Test Post']);

        $query = <<<GQL
{
    post(id: "{$post->id}") {
        id
        title
    }
}
GQL;

        $this
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['post' => [
                'id' => $post->id,
                'title' => 'Test Post',
            ]]]);
    }

    #[Test]
    public function it_returns_null_for_unpublished_model()
    {
        $post = Post::factory()->unpublished()->create();

        $query = <<<GQL
{
    post(id: "{$post->id}") {
        id
        title
    }
}
GQL;

        $this
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['post' => null]]);
    }

    #[Test]
    public function it_returns_unpublished_model_with_live_preview_token()
    {
        $post = Post::factory()->unpublished()->create(['title' => 'Unpublished Post']);

        LivePreview::tokenize('test-token', $post);

        $query = <<<GQL
{
    post(id: "{$post->id}") {
        id
        title
    }
}
GQL;

        $this
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['post' => null]]);

        $this
            ->post('/graphql?token=test-token', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['post' => [
                'id' => $post->id,
                'title' => 'Unpublished Post',
            ]]]);
    }

    #[Test]
    public function it_does_not_show_unpublished_model_with_token_for_different_model()
    {
        $post = Post::factory()->unpublished()->create();
        $otherPost = Post::factory()->create();

        LivePreview::tokenize('test-token', $otherPost);

        $query = <<<GQL
{
    post(id: "{$post->id}") {
        id
        title
    }
}
GQL;

        $this
            ->post('/graphql?token=test-token', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['post' => null]]);
    }
}
