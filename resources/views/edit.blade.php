@extends('statamic::layout')
@section('title', __('Edit :resource', [
    'resource' => $resource->singular(),
]))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="{{ $title }}"
        action="{{ $action }}"
        method="{{ $method }}"
        :resource-has-routes="{{ $resourceHasRoutes ? 'true' : 'false' }}"
        permalink="{{ $permalink }}"
        :is-creating="false"
        publish-container="base"
        :read-only="{{ $resource->readOnly() ? 'true' : 'false' }}"
        :resource='@json($resource->toArray())'
        create-another-url="{{ cp_route('runway.create', ['resourceHandle' => $resource->handle()]) }}"
        listing-url="{{ cp_route('runway.index', ['resourceHandle' => $resource->handle()]) }}"
    ></runway-publish-form>

    <script>
        window.Runway = {
            currentRecord: @json($currentRecord),
            currentResource: "{{ $resource->handle() }}",
        }
    </script>
@endsection
