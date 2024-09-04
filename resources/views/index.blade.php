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
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ $actionUrl }}"
        primary-column="{{ $primaryColumn }}"
        :has-publish-states="{{ Statamic\Support\Str::bool($resource->hasPublishStates()) }}"
    >
        <template #twirldown="{ actionCompleted }">
            @can('configure fields')
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $resource->handle()]) }}"></dropdown-item>
            @endcan
            <data-list-inline-actions
                item="{{ $resource->handle() }}"
                url="{{ cp_route('runway.actions.run', ['resource' => $resource->handle()]) }}"
                :actions="{{ $actions }}"
                @completed="actionCompleted"
            ></data-list-inline-actions>
        </template>
    </resource-view>

@endsection
