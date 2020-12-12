<?php

namespace DoubleThreeDigital\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SplFileInfo;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Text;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        Statamic::booted(function () {
            $modelDirectory = is_dir(app_path('Models')) ? app_path('Models') : app_path();

            $models = collect(File::allFiles($modelDirectory))
                ->reject(function (SplFileInfo $file) {
                    $fileContents = file_get_contents($file->getPathname());

                    return Str::contains($fileContents, 'trait') || Str::contains($fileContents, 'interface');
                })
                ->map(function (SplFileInfo $file) {
                    $fileContents = file_get_contents($file->getPathname());

                    $modelNamespace = strstr($fileContents, 'namespace ',);
                    $modelNamespace = strstr($modelNamespace, ';', true);
                    $modelNamespace = str_replace('namespace ', '', $modelNamespace);

                    $modelClassName = str_replace('.php', '', $file->getFilename());

                    $fullModelClassName = $modelNamespace.'\\'.$modelClassName;

                    $model = new $fullModelClassName();

                    return $model;
                })
                ->filter(function ($model) {
                    return $model instanceof Model;
                })
                ->map(function ($model) {
                    $blueprintFields = collect(Schema::getColumnListing($model->getTable()))
                        ->map(function ($columnName) {
                            return new Field($columnName, [
                                'type' => 'text',
                            ]);
                        })
                        ->toArray();

                    $blueprint = Blueprint::makeFromFields($blueprintFields);

                    return [
                        'model' => class_basename($model),
                        'blueprint' => $blueprint,
                    ];
                });
        });
    }
}
