@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', __('Create :resource', [
    'resource' => $resource->singular(),
]))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions='@json($actions)'
        method="post"
        :resource='@json($resource->toArray())'
        :resource-has-routes="{{ $str::bool($resourceHasRoutes) }}"
        initial-title="{{ $title }}"
        :initial-blueprint='@json($blueprint)'
        :initial-values='@json($values)'
        :initial-meta='@json($meta)'
        :breadcrumbs='{{ $breadcrumbs->toJson() }}'
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        initial-listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
    ></runway-publish-form>
@endsection
