@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', $breadcrumbs->title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        publish-container="base"
        :initial-actions='@json($actions)'
        method="patch"
        :resource='@json($resource->toArray())'
        :resource-has-routes="{{ $str::bool($resourceHasRoutes) }}"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        :initial-blueprint='@json($blueprint)'
        :initial-values='@json($values)'
        :initial-meta='@json($meta)'
        initial-permalink="{{ $permalink }}"
        :initial-read-only="{{ $str::bool($readOnly) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :can-edit-blueprint="{{ Auth::user()->can('configure fields') ? 'true' : 'false' }}"
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        initial-listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('runway.actions.run', ['resource' => $resource->handle()]) }}"
    ></runway-publish-form>

    <script>
        window.Runway = {
            currentModel: @json($currentModel),
            currentResource: "{{ $resource->handle() }}",
        }
    </script>
@endsection
