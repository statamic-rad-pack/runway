<?php

namespace StatamicRadPack\Runway\Tests\Fieldtypes;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\Fixtures\Scopes\TheHoff;
use StatamicRadPack\Runway\Tests\TestCase;

class RunwayModelsFieldtypeTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::make()->makeSuper()->save());
    }

    private function field(array $config = [], string $handle = 'selected_models'): Field
    {
        return new Field($handle, array_merge(['type' => 'runway_models', 'resource' => 'author'], $config));
    }

    #[Test]
    #[DataProvider('values')]
    public function normalizes_host_values_without_changing_keys($max, $value, $expected)
    {
        $fieldtype = $this->field(['max_items' => $max])->fieldtype();

        $this->assertSame($expected, $fieldtype->process($value));
        $this->assertSame($expected, $fieldtype->process($fieldtype->preProcess($expected)));
        $this->assertSame($expected, $fieldtype->toQueryableValue($value));
    }

    public static function values(): array
    {
        return [
            'single integer' => [1, [7], 7],
            'single string' => [1, ['007'], '007'],
            'single scalar' => [1, '007', '007'],
            'single empty' => [1, [], null],
            'zero key' => [1, [0], 0],
            'ordered plural' => [null, [7, '007', 3, 7, '3'], [7, '007', 3]],
            'empty plural' => [null, null, []],
        ];
    }

    #[Test]
    public function stores_json_attributes_without_touching_selected_models_or_mapping_a_relation()
    {
        $authors = Author::factory()->count(2)->create();
        $host = Post::factory()->create();
        $before = DB::table('authors')->get()->toJson();
        $field = $this->field([], 'values_recommended')->setParent($host);

        $host->setAttribute($host->getFieldColumn($field->handle()), $field->fieldtype()->process($authors->modelKeys()));
        $host->save();

        $this->assertSame($authors->modelKeys(), $host->fresh()->getFieldValue('values_recommended'));
        $this->assertSame($authors->modelKeys(), $field->fieldtype()->preProcess($host->fresh()->getFieldValue('values_recommended')));

        $host->setAttribute('values->recommended', $field->fieldtype()->process(array_reverse($authors->modelKeys())));
        $host->save();
        $this->assertSame(array_reverse($authors->modelKeys()), $host->fresh()->getFieldValue('values_recommended'));

        $host->setAttribute('values->recommended', $field->fieldtype()->process([]));
        $host->save();
        $this->assertSame([], $host->fresh()->getFieldValue('values_recommended'));
        $this->assertSame($before, DB::table('authors')->get()->toJson());

        $blueprint = Blueprint::find('runway::post')->ensureField('values_recommended', $field->config());
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($blueprint);
        $this->assertFalse(Runway::findResource('post')->eloquentRelationships()->has('values_recommended'));
    }

    #[Test]
    public function resolves_string_primary_keys_instead_of_route_keys()
    {
        $model = new class extends Author
        {
            protected $table = 'authors';
            protected $primaryKey = 'name';
            protected $keyType = 'string';
            public $incrementing = false;

            public function getRouteKeyName()
            {
                return 'id';
            }
        };
        Author::factory()->create(['name' => '007']);
        Author::factory()->create(['name' => 'f115f390-b2d1-458e-9c29-b9c249e27943']);
        Runway::registerResource($model::class, ['handle' => 'author', 'title_field' => 'name']);
        $fieldtype = $this->field()->fieldtype();
        $ids = ['f115f390-b2d1-458e-9c29-b9c249e27943', '007'];

        $this->assertSame($ids, collect($fieldtype->augment($ids))->map(fn ($model) => $model['name']->value())->all());
        $this->assertSame('007', $fieldtype->getItemData(['007'])->first()['id']);
    }

    #[Test]
    public function validates_and_resolves_plain_eloquent_hosts_without_runway_relationship_methods()
    {
        $author = Author::factory()->create();
        $post = Post::factory()->create(['values' => [9999, $author->id]]);
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'posts';
            protected $casts = ['values' => 'array'];
        };
        $host = $model->newQuery()->find($post->id);
        $fieldtype = $this->field([], 'values')->setParent($host)->fieldtype();

        $this->assertTrue(Validator::make(['models' => ['9999', $author->id]], ['models' => $fieldtype->rules()])->passes());
        $this->assertSame([$author->id], collect($fieldtype->toGqlType()['resolve']($host))->pluck('id')->all());
    }

    #[Test]
    public function saves_and_augments_content_ids_in_order()
    {
        Collection::make('model_selection')->save();
        $authors = Author::factory()->count(2)->create();
        $field = $this->field();
        $entry = Entry::make()->collection('model_selection')->slug('test')->blueprint('models');
        $ids = array_reverse($authors->modelKeys());
        $entry->set('selected_models', $field->fieldtype()->process($ids))->save();

        $this->assertSame($ids, $entry->fresh()->get('selected_models'));
        $this->assertSame($ids, collect($entry->augmentedValue('selected_models')->value())->map(fn ($model) => $model['id']->value())->all());
    }

    #[Test]
    public function validates_new_references_but_preserves_saved_unavailable_ids()
    {
        $author = Author::factory()->create();
        $host = Post::factory()->create(['values' => ['selected' => [9999]]]);
        $field = $this->field([], 'values_selected')->setParent($host);
        $rules = $field->fieldtype()->rules();

        $this->assertTrue(Validator::make(['models' => [9999, $author->id]], ['models' => $rules])->passes());
        $this->assertTrue(Validator::make(['models' => [9999]], ['models' => $rules])->passes());
        $this->assertTrue(Validator::make(['models' => ['9999']], ['models' => $rules])->passes());
        $this->assertFalse(Validator::make(['models' => [9998]], ['models' => $rules])->passes());

        $host->values = ['selected' => [9998]];
        $this->assertFalse(Validator::make(['models' => [9998]], ['models' => $rules])->passes());

        $this->actingAs(User::make()->save());
        $this->assertTrue(Validator::make(['models' => [9999]], ['models' => $rules])->passes());
        $this->assertFalse(Validator::make(['models' => [$author->id]], ['models' => $rules])->passes());
    }

    #[Test]
    public function selection_enforces_scopes_permissions_and_missing_placeholders()
    {
        TheHoff::register();
        $author = Author::factory()->create(['name' => 'David Hasselhoff']);
        $other = Author::factory()->create(['name' => 'Someone Else']);
        $fieldtype = $this->field(['query_scopes' => ['the_hoff'], 'title_format' => '{{ name }}!'])->fieldtype();

        $items = $fieldtype->getIndexItems(new FilteredRequest(['search' => 'Hasselhoff', 'paginate' => false]));
        $this->assertSame([$author->id], $items->modelKeys());
        $this->assertSame('David Hasselhoff!', $fieldtype->getItemData([$author->id])->first()['title']);
        $this->assertTrue($fieldtype->getItemData([$other->id])->first()['invalid']);
        $this->assertFalse(Validator::make(['models' => [$other->id]], ['models' => $fieldtype->rules()])->passes());

        Role::make('reader')->permissions(['view author'])->save();
        $this->actingAs(User::make()->assignRole('reader')->save());
        $this->assertSame('David Hasselhoff!', $fieldtype->getItemData([$author->id])->first()['title']);
        $this->assertFalse($fieldtype->getItemData([$author->id])->first()['editable']);
        $this->assertFalse($fieldtype->preload()['canCreate']);
        $this->assertFalse($fieldtype->preload()['canEdit']);

        $this->actingAs(User::make()->save());
        $this->assertCount(0, $fieldtype->getIndexItems(new FilteredRequest));
        $this->assertTrue($fieldtype->getItemData([$author->id])->first()['invalid']);
        $meta = $fieldtype->preload();
        $this->assertFalse($meta['canCreate']);
        $this->assertFalse($meta['canEdit']);
        $this->assertSame([], $meta['creatables']);
    }

    #[Test]
    public function search_indexes_respect_the_resource_and_selection_scopes()
    {
        TheHoff::register();
        Config::set('statamic.search.indexes.models', [
            'driver' => 'local',
            'searchables' => ['runway:author', 'runway:post'],
            'fields' => ['name', 'title'],
        ]);

        $author = Author::factory()->create(['name' => 'David Hasselhoff']);
        Author::factory()->create(['name' => 'David Jones']);
        Post::factory()->create(['id' => $author->id, 'title' => 'David Hasselhoff']);
        $fieldtype = $this->field(['search_index' => 'models', 'query_scopes' => ['the_hoff']])->fieldtype();
        $items = $fieldtype->getIndexItems(new FilteredRequest(['search' => 'David', 'perPage' => 1]));

        $this->assertSame(1, $items->total());
        $this->assertSame([$author->id], $items->getCollection()->pluck('id')->all());
        $this->assertInstanceOf(Author::class, $items->first());
    }

    #[Test]
    #[DataProvider('nestedHosts')]
    public function preserves_unavailable_nested_ids_when_rows_are_reordered($type, $rows)
    {
        Collection::make('model_selection')->save();
        $entry = Entry::make()->collection('model_selection')->slug('nested')->blueprint('models')->data(['nested' => $rows]);
        $entry->save();
        $selection = [['handle' => 'selected', 'field' => ['type' => 'runway_models', 'resource' => 'author']]];
        $config = $type === 'grid' ? ['fields' => $selection] : ['sets' => ['authors' => ['fields' => $selection]]];
        $fields = new Fields([['handle' => 'nested', 'field' => ['type' => $type, ...$config]]], $entry);

        $values = $fields->addValues(['nested' => $rows])->preProcess()->values()->get('nested');
        Blink::flush();
        $fields->addValues(['nested' => array_reverse($values)])->validator()->validate();

        $this->assertSame($rows, $entry->fresh()->get('nested'));

        data_set($values, $type === 'bard' ? '0.attrs.values.selected' : '0.selected', [9997]);
        Blink::flush();
        $this->expectException(ValidationException::class);
        $fields->addValues(['nested' => $values])->validator()->validate();
    }

    public static function nestedHosts(): array
    {
        return [
            'grid' => ['grid', [['id' => 'a', 'selected' => [9999]], ['id' => 'b', 'selected' => [9998]]]],
            'grid without row IDs' => ['grid', [['selected' => [9999]], ['selected' => [9998]]]],
            'grid with mixed row IDs' => ['grid', [['id' => 'a', 'selected' => [9999]], ['selected' => [9998]]]],
            'replicator' => ['replicator', [['id' => 'a', 'type' => 'authors', 'selected' => [9999]], ['id' => 'b', 'type' => 'authors', 'selected' => [9998]]]],
            'replicator without row IDs' => ['replicator', [['type' => 'authors', 'selected' => [9999]], ['type' => 'authors', 'selected' => [9998]]]],
            'bard' => ['bard', [
                ['type' => 'set', 'attrs' => ['id' => 'a', 'values' => ['type' => 'authors', 'selected' => [9999]]]],
                ['type' => 'set', 'attrs' => ['id' => 'b', 'values' => ['type' => 'authors', 'selected' => [9998]]]],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('workingCopyHosts')]
    public function preserves_unavailable_references_from_saved_working_copies($type)
    {
        Config::set('statamic.revisions.enabled', true);
        $live = ['selected' => [], 'rows' => []];
        $saved = ['selected' => [9999], 'rows' => [['id' => 'a', 'selected' => [9998]]]];

        if ($type === 'entry') {
            Collection::make('model_selection')->revisionsEnabled(true)->save();
            $host = Entry::make()->collection('model_selection')->slug('working')->blueprint('models')->data($live);
            $host->save();
            $copy = (clone $host)->data($saved);
        } else {
            $host = Post::factory()->create(['values' => $live]);
            $copy = clone $host;
            $copy->values = $saved;
        }

        $revision = $copy->makeWorkingCopy();
        $revision->save();

        try {
            $parent = $host->fromWorkingCopy();
            $handle = $type === 'entry' ? 'selected' : 'values_selected';
            $fieldtype = $this->field([], $handle)->setParent($parent)->fieldtype();
            $this->assertTrue(Validator::make(['models' => [9999]], ['models' => $fieldtype->rules()])->passes());

            if ($type === 'entry') {
                $parent->set('selected', [9997]);
            } else {
                $parent->values = [...$saved, 'selected' => [9997]];
            }

            $this->assertFalse(Validator::make(['models' => [9997]], ['models' => $fieldtype->rules()])->passes());

            $handle = $type === 'entry' ? 'rows' : 'values_rows';
            $fields = new Fields([['handle' => $handle, 'field' => ['type' => 'grid', 'fields' => [
                ['handle' => 'selected', 'field' => ['type' => 'runway_models', 'resource' => 'author']],
            ]]]], $parent);
            $values = $fields->addValues([$handle => $saved['rows']])->preProcess()->values()->all();
            $fields->addValues($values)->validator()->validate();

            $this->assertSame($live, $type === 'entry' ? $host->fresh()->data()->except('blueprint')->all() : $host->fresh()->values);
        } finally {
            $revision->delete();
        }
    }

    public static function workingCopyHosts(): array
    {
        return [['entry'], ['model']];
    }

    #[Test]
    public function augmentation_omits_unpublished_and_missing_records_without_changing_storage()
    {
        $published = Post::factory()->create();
        $draft = Post::factory()->unpublished()->create();
        $fieldtype = $this->field(['resource' => 'post'])->fieldtype();
        $ids = [$draft->id, 99999, $published->id];

        $this->assertSame([$published->id], collect($fieldtype->augment($ids))->map(fn ($model) => $model['id']->value())->all());
        $this->assertSame($ids, $fieldtype->process($ids));
        $this->assertNull($this->field(['resource' => 'post', 'max_items' => 1])->fieldtype()->augment($draft->id));
    }

    #[Test]
    public function augmentation_batches_caches_missing_ids_and_reuses_loaded_models()
    {
        $authors = Author::factory()->count(3)->create();
        $fieldtype = $this->field(['with' => []])->fieldtype();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $result = $fieldtype->augment([$authors[2]->id, 9999, $authors[0]->id]);
        $fieldtype->augment([9999, $authors[0]->id]);
        $fieldtype->augment([$authors[1]]);

        $this->assertSame([$authors[2]->id, $authors[0]->id], collect($result)->map(fn ($model) => $model['id']->value())->all());
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
    }

    #[Test]
    public function batches_nested_grid_siblings()
    {
        Collection::make('model_selection')->save();
        $authors = Author::factory()->count(4)->create();
        $entry = Entry::make()->collection('model_selection')->slug('nested')->blueprint('models')->data([
            'rows' => $authors->map(fn ($author) => ['selected_models' => [$author->id]])->all(),
        ]);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $ids = collect($entry->augmentedValue('rows')->value())->map(fn ($row) => $row['selected_models'][0]['id']->value())->all();

        $this->assertSame($authors->modelKeys(), $ids);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
    }

    #[Test]
    public function filters_json_host_values_without_a_relationship_method()
    {
        $author = Author::factory()->create(['name' => 'Selected']);
        $other = Author::factory()->create(['name' => 'Other']);
        $match = Post::factory()->create(['values' => ['single' => $author->id, 'selected' => [$other->id, (string) $author->id]]]);
        Post::factory()->create(['values' => ['single' => $other->id, 'selected' => [$other->id]]]);
        Post::factory()->create(['values' => ['selected' => []]]);
        $fieldtype = $this->field([], 'values_selected')->fieldtype();
        $query = Post::query();
        $fieldtype->filter()->apply($query, 'values_selected', ['field' => 'name', 'operator' => '=', 'value' => 'Selected']);

        $this->assertSame([$match->id], $query->pluck('id')->all());

        $query = Post::query();
        $fieldtype->filter()->apply($query, 'values_selected', ['field' => 'name', 'operator' => '=', 'value' => 'Absent']);
        $this->assertSame([], $query->pluck('id')->all());

        $query = Post::query();
        $this->field(['max_items' => 1], 'values_single')->fieldtype()->filter()->apply($query, 'values_single', ['field' => 'name', 'operator' => '=', 'value' => 'Selected']);
        $this->assertSame([$match->id], $query->pluck('id')->all());
    }

    #[Test]
    public function graphql_resolves_stored_ids_with_singular_and_plural_shapes()
    {
        $authors = Author::factory()->count(2)->create();
        $host = Post::factory()->create(['values' => ['single' => $authors[1]->id, 'many' => [$authors[1]->id, $authors[0]->id]]]);

        foreach ([['values_single', 1, $authors[1]->id], ['values_many', null, array_reverse($authors->modelKeys())]] as [$handle, $max, $expected]) {
            $fieldtype = $this->field(['max_items' => $max], $handle)->fieldtype();
            $type = $fieldtype->toGqlType();
            $models = $type['resolve']($host);
            $actual = $max === 1 ? $models->id : collect($models)->pluck('id')->all();
            $this->assertSame($expected, $actual);
            $this->assertSame($max === 1 ? 'runway_graphql_types_author' : '[runway_graphql_types_author]', (string) $type['type']);

            $models = $type['resolve'](collect([$handle => $fieldtype->augment($host->getFieldValue($handle))]));
            $this->assertSame($expected, $max === 1 ? $models->id : collect($models)->pluck('id')->all());

            $asset = Asset::make()->set($handle, $host->getFieldValue($handle));
            $models = $type['resolve']($asset);
            $this->assertSame($expected, $max === 1 ? $models->id : collect($models)->pluck('id')->all());
        }
    }
}
