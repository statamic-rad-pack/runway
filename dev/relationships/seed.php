<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint as Table;
use Illuminate\Support\Facades\Schema;
use RunwayRelationshipsSandbox\Author;
use RunwayRelationshipsSandbox\Notebook;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Fieldset;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Role;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;

$site = __DIR__.'/site';
if (! is_file($site.'/artisan') || realpath($site) !== __DIR__.'/site') {
    throw new RuntimeException('Expected the isolated relationships site.');
}

touch($site.'/database/database.sqlite');
require $site.'/vendor/autoload.php';
$app = require $site.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (realpath(config('database.connections.sqlite.database')) !== realpath($site.'/database/database.sqlite')) {
    throw new RuntimeException('Refusing to reset a database outside the relationships site.');
}

if (is_file($site.'/content/collections/pages/home.md')) {
    unlink($site.'/content/collections/pages/home.md');
}
\Statamic\Facades\Stache::clear();

Schema::dropIfExists('authors');
Schema::dropIfExists('notebooks');
Schema::create('authors', function (Table $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
Schema::create('notebooks', function (Table $table) {
    $table->id();
    $table->string('title');
    $table->json('selections')->nullable();
    $table->timestamps();
});

foreach (['Ada Lovelace', 'Grace Hopper', 'Katherine Johnson'] as $name) {
    Author::query()->create(['name' => $name]);
}

$selector = ['type' => 'runway_models', 'resource' => 'author'];
$fields = [
    ['handle' => 'title', 'field' => ['type' => 'text', 'validate' => 'required']],
    ['handle' => 'single', 'field' => [...$selector, 'display' => 'Featured author', 'max_items' => 1]],
    ['handle' => 'ordered', 'field' => [...$selector, 'display' => 'Ordered authors', 'reorderable' => true]],
    ['handle' => 'unordered', 'field' => [...$selector, 'display' => 'Authors (not reorderable)']],
    ['handle' => 'dropdown', 'field' => [...$selector, 'display' => 'Dropdown', 'mode' => 'select']],
    ['handle' => 'typeahead', 'field' => [...$selector, 'display' => 'Typeahead', 'mode' => 'typeahead', 'max_items' => 1]],
    ['handle' => 'unavailable', 'field' => [...$selector, 'display' => 'Unavailable author']],
    ['handle' => 'rows', 'field' => ['type' => 'grid', 'fields' => [['import' => 'model_selection']]]],
    ['handle' => 'blocks', 'field' => ['type' => 'replicator', 'sets' => ['main' => ['sets' => ['authors' => ['fields' => [['import' => 'model_selection']]]]]]]],
    ['handle' => 'body', 'field' => ['type' => 'bard', 'sets' => ['main' => ['sets' => ['authors' => ['fields' => [['import' => 'model_selection']]]]]]]],
];

Fieldset::make('model_selection')->setContents(['fields' => [
    ['handle' => 'selected', 'field' => [...$selector, 'reorderable' => true]],
]])->save();

$contents = ['tabs' => ['main' => ['sections' => [['fields' => $fields]]]]];
Blueprint::make('models')->setNamespace('collections/pages')->setContents($contents)->save();
Blueprint::make('author')->setNamespace('runway')->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required', 'listable' => true]],
]]]]]])->save();
Blueprint::make('notebook')->setNamespace('runway')->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
    ['handle' => 'title', 'field' => ['type' => 'text', 'listable' => true]],
    ['handle' => 'selections_single', 'field' => [...$selector, 'max_items' => 1]],
    ['handle' => 'selections_ordered', 'field' => [...$selector, 'reorderable' => true]],
]]]]]])->save();

Collection::make('pages')->title('Acceptance pages')->routes('/{slug}')->save();
Entry::make()->id('models-acceptance')->collection('pages')->blueprint('models')->slug('models')->data([
    'title' => 'Runway Models acceptance',
    'template' => 'acceptance-blade',
    'single' => 1,
    'ordered' => [3, 1],
    'unordered' => [1, 2],
    'dropdown' => [2],
    'typeahead' => 3,
    'unavailable' => [9999],
    'rows' => [['id' => 'grid-row', 'selected' => [3, 2]]],
    'blocks' => [['id' => 'replicator-set', 'type' => 'authors', 'selected' => [2, 1]]],
    'body' => [['type' => 'set', 'attrs' => ['id' => 'bard-set', 'values' => ['type' => 'authors', 'selected' => [1, 3]]]]],
])->save();

Notebook::query()->create(['title' => 'JSON IDs — no relation methods', 'selections' => ['single' => 2, 'ordered' => [3, 1]]]);
Taxonomy::make('topics')->title('Topics')->save();
Blueprint::make('topic')->setNamespace('taxonomies/topics')->setContents($contents)->save();
Term::make()->taxonomy('topics')->slug('models')->data(['title' => 'Models term', 'single' => 1, 'ordered' => [3, 2]])->save();
GlobalSet::make('models')->title('Models global')->save();
Blueprint::make('models')->setNamespace('globals')->setContents($contents)->save();
GlobalSet::find('models')->in('default')->data(['title' => 'Models global', 'single' => 2, 'ordered' => [2, 1]])->save();

Role::make('viewer')->title('Model viewer')->permissions(['access cp', 'view author'])->save();
($admin = User::findByEmail('admin@example.test') ?? User::make()->email('admin@example.test'))->password('password')->makeSuper()->save();
(User::findByEmail('viewer@example.test') ?? User::make()->email('viewer@example.test'))->password('password')->assignRole('viewer')->save();
Blueprint::find('user')->ensureField('single', [...$selector, 'max_items' => 1])->ensureField('ordered', $selector)->save();
$admin->set('single', 1)->set('ordered', [3, 2])->save();
Blueprint::find('assets/assets')->ensureField('single', [...$selector, 'max_items' => 1])->ensureField('ordered', $selector)->save();
file_put_contents($site.'/public/assets/fixture.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="navy"/></svg>');
\Statamic\Facades\Asset::find('assets::fixture.svg')->set('single', 2)->set('ordered', [1, 3])->save();

file_put_contents($site.'/resources/views/acceptance.antlers.html', <<<'HTML'
<!doctype html><title>Runway Models acceptance</title>
<h1>Runway Models acceptance</h1>
{{ collection:pages }}
<h2>{{ title }}</h2>
Featured: {{ single:name }}
<ol>{{ ordered }}<li>{{ name }}</li>{{ /ordered }}</ol>
{{ /collection:pages }}
HTML);

file_put_contents($site.'/resources/views/acceptance-blade.blade.php', <<<'HTML'
<!doctype html><title>Runway Models Blade acceptance</title>
<h1>Runway Models Blade acceptance</h1>
Featured: {{ $single['name'] ?? '' }}
<ol>
@foreach ($ordered as $author)
    <li>{{ $author['name'] }}</li>
@endforeach
</ol>
HTML);

echo "Seeded isolated Runway Models entry, term, global, JSON host, nesting and permissions.\n";
