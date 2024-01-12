@extends('statamic::layout')
@section('title', __('Create :resource', [
    'resource' => $resource->singular(),
]))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :initial-actions="{{ json_encode($actions) }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="{{ $title }}"
        method="{{ $method }}"
        :resource-has-routes="{{ $resourceHasRoutes ? 'true' : 'false' }}"
        :is-creating="true"
        publish-container="base"
        :resource='@json($resource->toArray())'
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
    ></runway-publish-form>
@endsection
