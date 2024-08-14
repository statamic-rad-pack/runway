@extends('statamic::layout')
@section('title', $resource->name())
@section('wrapper_class', 'max-w-full')

@section('content')

    <resource-view
        title="{{ $resource->name() }}"
        handle="{{ $resource->handle() }}"
        :can-create="{{ Statamic\Support\Str::bool($canCreate) }}"
        create-url="{{ $createUrl }}"
        create-label="{{ $createLabel }}"
        sort-column="{{ $resource->orderBy() }}"
        sort-direction="{{ $resource->orderByDirection() }}"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ $actionUrl }}"
        primary-column="{{ $primaryColumn }}"
        :has-publish-states="{{ Statamic\Support\Str::bool($resource->hasPublishStates()) }}"
    ></resource-view>

@endsection
