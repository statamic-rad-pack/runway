<?php

namespace StatamicRadPack\Runway\Tests\Http\Controllers\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class RestoreModelRevisionController extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic.revisions.enabled' => true]);
    }

    protected function tearDown(): void
    {
        Folder::delete(__DIR__.'/../../../__fixtures__/revisions');

        parent::tearDown();
    }

    #[Test]
    public function it_restores_revision()
    {
        $model = Post::factory()->create(['title' => 'Some new title']);

        $revision = $model->makeRevision()
            ->message('The revision')
            ->attributes([
                'id' => $model->id,
                'published' => true,
                'data' => collect($model->getAttributes())->merge([
                    'title' => 'Original title',
                ])->all(),
            ]);

        $revision->save();

        $this
            ->actingAs(User::make()->id('user-1')->makeSuper()->save())
            ->post($model->runwayRestoreRevisionUrl(), ['revision' => $revision->date()->timestamp])
            ->assertOk()
            ->assertSessionHas('success');

        $workingCopy = $model->workingCopy();

        $this->assertEquals($model->id, $workingCopy->attributes()['id']);
        $this->assertEquals('Original title', $workingCopy->attributes()['data']['title']);
    }
}
