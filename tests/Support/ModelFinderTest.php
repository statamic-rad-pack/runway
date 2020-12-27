<?php

namespace DoubleThreeDigital\Runway\Tests\Support;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Statamic\Facades\Blueprint;

class ModelFinderTest extends TestCase
{
    /** @test */
    public function can_get_all_models()
    {
        $models = ModelFinder::all();

        $model = $models->first();
        $blueprint = Blueprint::make()->setContents(config('runway.models')[Post::class]['blueprint']);

        $this->assertSame($model['_handle'], 'post');
        $this->assertSame($model['model'], Post::class);
        $this->assertSame($model['name'], 'Posts');
        $this->assertSame($model['singular'], 'Post');
        $this->assertSame($model['blueprint']->toPublishArray(), $blueprint->toPublishArray());
        $this->assertSame($model['listing_columns'], ['title']);
        $this->assertSame($model['listing_sort'], [
            'column' => 'title',
            'direction' => 'desc',
        ]);
        $this->assertSame($model['primary_key'], 'id');
        $this->assertSame($model['route_key'], 'id');
        $this->assertSame($model['model_table'], 'posts');
        $this->assertSame($model['schema_columns'], []);
    }

    /** @test */
    public function can_find_model()
    {
        $model = ModelFinder::find('post');

        $this->assertIsArray($model);

        $this->assertArrayHasKey('_handle', $model);
        $this->assertArrayHasKey('model', $model);
    }
}
