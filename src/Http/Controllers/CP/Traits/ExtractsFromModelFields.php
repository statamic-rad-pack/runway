<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP\Traits;

use Illuminate\Database\Eloquent\Model;
use Statamic\Fields\Blueprint;
use StatamicRadPack\Runway\Resource;

trait ExtractsFromModelFields
{
    use PreparesModels;

    protected function extractFromFields(Model $model, Resource $resource, Blueprint $blueprint)
    {
        $values = $this->prepareModelForPublishForm($resource, $model);

        $fields = $blueprint
            ->fields()
            ->setParent($model)
            ->addValues($values->all())
            ->preProcess();

        $values = $fields->values()->merge([
            'id' => $model->getKey(),
            'published' => $model->{$resource->publishedColumn()},
        ]);

        return [$values->all(), $fields->meta()->all()];
    }
}
