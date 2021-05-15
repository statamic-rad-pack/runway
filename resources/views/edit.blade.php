@extends('statamic::layout')
@section('title', "Edit {$resource->singular()}")
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="Edit {{ $resource->singular() }}"
        action="{{ $action }}"
        method="post"
        :resource-has-routes="{{ $resource->route() !== null }}"
        permalink="{{ $permalink }}"
        :is-creating="false"
    ></runway-publish-form>
@endsection
