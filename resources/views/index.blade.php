@extends('statamic::layout')
@section('title', $resource->name())

@section('content')
    <runway-resource-view
        icon="{{ $icon }}"
        title="{{ $resource->name() }}"
        handle="{{ $resource->handle() }}"
        :can-create="{{ Statamic\Support\Str::bool($canCreate) }}"
        create-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        create-label="{{ __('Create :resource', ['resource' => $resource->singular()]) }}"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :actions="{{ $actions->toJson() }}"
        action-url="{{ cp_route('runway.actions.run', ['resource' => $resource->handle()]) }}"
        models-action-url="{{ cp_route('runway.models.actions.run', ['resource' => $resource->handle()]) }}"
        blueprint-url="{{ cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $resource->handle()]) }}"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($canEditBlueprint) }}"
        :has-publish-states="{{ Statamic\Support\Str::bool($hasPublishStates) }}"
        title-column="{{ $titleColumn }}"
    ></runway-resource-view>
@endsection
