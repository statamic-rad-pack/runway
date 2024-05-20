@extends('statamic::layout')
@section('title', __('Edit :resource', [
    'resource' => $resource->singular(),
]))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        initial-reference="{{ $currentModel['reference'] }}"
        :initial-actions="{{ json_encode($actions) }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="{{ $title }}"
        method="{{ $method }}"
        :resource-has-routes="{{ $resourceHasRoutes ? 'true' : 'false' }}"
        permalink="{{ $permalink }}"
        :is-creating="false"
        publish-container="base"
        :read-only="{{ $resource->readOnly() ? 'true' : 'false' }}"
        :resource='@json($resource->toArray())'
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
        :can-edit-blueprint="{{ Auth::user()->can('configure fields') ? 'true' : 'false' }}"
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
