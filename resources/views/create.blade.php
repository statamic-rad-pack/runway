@extends('statamic::layout')
@section('title', "Create {$resource->singular()}")
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <runway-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="Create {{ $resource->singular() }}"
        action="{{ $action }}"
        method="post"
        :resource-has-routes="{{ $resource->hasRouting() ? 'true' : 'false' }}"
        :is-creating="true"
    ></runway-publish-form>
@endsection
