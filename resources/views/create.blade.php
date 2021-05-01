@extends('statamic::layout')
@section('title', "Create {$resource->singular()}")
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <publish-form
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        title="Create {{ $resource->singular() }}"
        name="create-publish-form"
        action="{{ $action }}"
        method="post"
    ></publish-form>
@endsection
