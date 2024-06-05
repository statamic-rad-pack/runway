<?php

namespace StatamicRadPack\Runway\Tests\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use StatamicRadPack\Runway\Actions\DuplicateModel;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class DuplicateModelTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    /** @test */
    public function it_returns_title()
    {
        $this->assertEquals('Duplicate', DuplicateModel::title());
    }

    /** @test */
    public function is_visible_to_eloquent_model()
    {
        $visibleTo = (new DuplicateModel())->visibleTo(Post::factory()->create());

        $this->assertTrue($visibleTo);
    }

    /** @test */
    public function is_not_visible_to_eloquent_model_when_resource_is_read_only()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.read_only', true);
        Runway::discoverResources();

        $visibleTo = (new DuplicateModel())->visibleTo(Post::factory()->create());

        $this->assertFalse($visibleTo);
    }

    /** @test */
    public function is_not_visible_to_eloquent_model_when_resource_creation_is_not_allowed()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->setHidden(true));

        Runway::discoverResources();

        $visibleTo = (new DuplicateModel())->visibleTo(Post::factory()->create());

        $this->assertFalse($visibleTo);
    }

    /** @test */
    public function is_not_visible_to_eloquent_model_without_a_runway_resource()
    {
        $model = new class extends Model
        {
            protected $table = 'posts';
        };

        $visibleTo = (new DuplicateModel())->visibleTo(new $model);

        $this->assertFalse($visibleTo);
    }

    /** @test */
    public function is_not_visible_to_entry()
    {
        Collection::make('posts')->save();

        $visibleTo = (new DuplicateModel())->visibleTo(
            tap(Entry::make()->collection('posts')->slug('hello-world'))->save()
        );

        $this->assertFalse($visibleTo);
    }

    /** @test */
    public function is_visible_to_eloquent_models_in_bulk()
    {
        $posts = Post::factory()->count(3)->create();

        $visibleToBulk = (new DuplicateModel())->visibleToBulk($posts);

        $this->assertTrue($visibleToBulk);
    }

    /** @test */
    public function is_not_visible_to_entries_in_bulk()
    {
        Collection::make('posts')->save();

        $entries = collect([
            tap(Entry::make()->collection('posts')->slug('hello-world'))->save(),
            tap(Entry::make()->collection('posts')->slug('foo-bar'))->save(),
            tap(Entry::make()->collection('posts')->slug('bye-bye'))->save(),
        ]);

        $visibleToBulk = (new DuplicateModel())->visibleToBulk($entries);

        $this->assertFalse($visibleToBulk);
    }

    /** @test */
    public function super_user_is_authorized()
    {
        $user = User::make()->makeSuper()->save();

        $authorize = (new DuplicateModel())->authorize($user, Post::factory()->create());

        $this->assertTrue($authorize);
    }

    /** @test */
    public function user_with_permission_is_authorized()
    {
        Role::make('editor')->addPermission('create post')->save();

        $user = User::make()->assignRole('editor')->save();

        $authorize = (new DuplicateModel())->authorize($user, Post::factory()->create());

        $this->assertTrue($authorize);

        Role::find('editor')->delete();
    }

    /** @test */
    public function user_without_permission_is_not_authorized()
    {
        $user = User::make()->save();

        $authorize = (new DuplicateModel())->authorize($user, Post::factory()->create());

        $this->assertFalse($authorize);
    }

    /** @test */
    public function it_duplicates_models()
    {
        $post = Post::factory()->create(['title' => 'Hello World']);

        $this->assertCount(1, Post::where('title', 'like', 'Hello World%')->get());

        (new DuplicateModel)->run(collect([$post]), []);

        $this->assertCount(2, Post::where('title', 'like', 'Hello World%')->get());

        $duplicate = Post::query()->whereNot('id', $post->id)->first();

        $this->assertFalse($duplicate->published());
    }
}
