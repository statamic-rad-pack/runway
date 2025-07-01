@use(Statamic\CP\Breadcrumbs\Breadcrumbs)
@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Breadcrumbs::title($title))

@section('content')
    <runway-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="{{ $method }}"
        :resource="{{ json_encode($resource->toArray()) }}"
        :resource-has-routes="{{ $str::bool($resourceHasRoutes) }}"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        initial-status="{{ $status }}"
        :initial-blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        initial-permalink="{{ $permalink }}"
        :initial-is-working-copy="{{ $str::bool($hasWorkingCopy) }}"
        :revisions-enabled="{{ $str::bool($revisionsEnabled) }}"
        :initial-read-only="{{ $str::bool($readOnly) }}"
        :can-edit-blueprint="{{ $str::bool($canEditBlueprint) }}"
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        initial-listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('runway.models.actions.run', ['resource' => $resource->handle()]) }}"
    ></runway-publish-form>

{{--    TODO: Do away with the window object--}}
    <script>
        window.Runway = {
            currentModel: @json($currentModel),
            currentResource: "{{ $resource->handle() }}",
        }
    </script>
@endsection
