<?php

namespace StatamicRadPack\Runway\Tests\Http\Controllers\CP;

use Facades\Statamic\CP\LivePreview;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ModelPreviewControllerTest extends TestCase
{
    #[Test]
    public function it_creates_a_token_with_model_for_editing()
    {
        Post::factory()->create([
            'slug' => 'existing-post',
        ]);

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('test-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $model) {
            return $token === null
                && $model->id === 1
                && $model->getSupplement('title') === 'The first post'
                && $model->getSupplement('live_preview') === ['foo' => 'bar'];
        })->andReturn($token);

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/runway/post/1/preview', [
                'preview' => [
                    'title' => 'The first post',
                    'slug' => 'the-first-post',
                ],
                'extras' => [
                    'foo' => 'bar',
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'test-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/posts\/existing-post\?live-preview=\w{16}&token=test-token$/',
            $response['url']
        );
    }

    #[Test]
    public function it_updates_existing_token_with_model_for_editing()
    {
        Post::factory()->create([
            'slug' => 'existing-post',
        ]);

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('existing-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $model) {
            return $token === 'existing-token'
                && $model->id === 1
                && $model->getSupplement('title') === 'The first post'
                && $model->getSupplement('live_preview') === ['foo' => 'bar'];
        })->andReturn($token);

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/runway/post/1/preview', [
                'token' => 'existing-token',
                'preview' => [
                    'title' => 'The first post',
                    'slug' => 'the-first-post',
                ],
                'extras' => [
                    'foo' => 'bar',
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'existing-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/posts\/existing-post\?live-preview=\w{16}&token=existing-token$/',
            $response['url']
        );
    }

    #[Test]
    public function it_sets_live_preview_to_true_if_theres_no_additional_data()
    {
        Post::factory()->create([
            'slug' => 'existing-post',
        ]);

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('existing-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $model) {
            return $token === 'existing-token'
                && $model->id === 1
                && $model->getSupplement('title') === 'The first post'
                && $model->getSupplement('live_preview') === true;
        })->andReturn($token);

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/runway/post/1/preview', [
                'token' => 'existing-token',
                'preview' => [
                    'title' => 'The first post',
                    'slug' => 'the-first-post',
                ],
                'extras' => [],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'existing-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/posts\/existing-post\?live-preview=\w{16}&token=existing-token$/',
            $response['url']
        );
    }
}
