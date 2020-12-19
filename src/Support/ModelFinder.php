<?php

namespace DoubleThreeDigital\Runway\Support;

use DoubleThreeDigital\Runway\Exceptions\ModelNotFound;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SplFileInfo;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;

class ModelFinder
{
    protected static array $models;

    public static function bootModels()
    {
        // $modelDirectory = is_dir(app_path('Models')) ? app_path('Models') : app_path();

        // $models = collect(File::allFiles($modelDirectory))
        //     ->reject(function (SplFileInfo $file) {
        //         $fileContents = file_get_contents($file->getPathname());

        //         return Str::contains($fileContents, 'trait') || Str::contains($fileContents, 'interface');
        //     })
        //     ->map(function (SplFileInfo $file) {
        //         $fileContents = file_get_contents($file->getPathname());

        //         $modelNamespace = strstr($fileContents, 'namespace ',);
        //         $modelNamespace = strstr($modelNamespace, ';', true);
        //         $modelNamespace = str_replace('namespace ', '', $modelNamespace);

        //         $modelClassName = str_replace('.php', '', $file->getFilename());

        //         $fullModelClassName = $modelNamespace.'\\'.$modelClassName;

        //         $model = new $fullModelClassName();

        //         return $model;
        //     })
        //     ->filter(function ($model) {
        //         return $model instanceof Model;
        //     })
        //     ->map(function ($model) {
        //         $blueprintFields = collect(Schema::getColumnListing($model->getTable()))
        //             ->map(function ($columnName) {
        //                 return new Field($columnName, [
        //                     'type' => 'text',
        //                 ]);
        //             })
        //             ->toArray();

        //         $blueprint = Blueprint::makeFromFields($blueprintFields);

        //         return [
        //             'model' => class_basename($model),
        //             'blueprint' => $blueprint,
        //         ];
        //     });

        static::$models = collect(config('runway.models'))
            ->map(function ($config, $model) {
                $blueprint = is_string($config['blueprint'])
                    ? Blueprint::find($config['blueprint'])
                    : Blueprint::make()->setContents($config['blueprint']);

                return [
                    '_handle'           => Str::lower(class_basename($model)),
                    'model'             => $model,
                    'name'              => $config['name'],
                    'singular'          => Str::singular($config['name']),
                    'blueprint'         => $blueprint,
                    'listing_columns'   => $config['listing']['columns'],
                    'listing_sort'      => $config['listing']['sort'],
                    'primary_key'       => (new $model())->getKeyName(),
                    'model_table'       => $modelTable = (new $model())->getTable(),
                    'schema_columns'    => Schema::getColumnListing($modelTable),
                ];
            })
            ->toArray();
    }

    public static function all(): ?Collection
    {
        return collect(static::$models);
    }

    public static function find(string $modelHandle): ?array
    {
        $model = collect(static::$models)
            ->where('_handle', $modelHandle)
            ->first();

        if (! $model) {
            throw new ModelNotFound("Runway could not find the model ({$modelHandle}). Please make sure it's configured correctly and you're using the correct handle.");
        }

        return $model;
    }
}
