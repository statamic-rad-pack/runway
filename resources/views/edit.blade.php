@extends('statamic::layout')
@section('title', "Edit {$resource->singular()}")
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <publish-form
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        title="Edit {{ $resource->singular() }}"
        name="edit-publish-form"
        action="{{ $action }}"
        method="post"
    ></publish-form>
@endsection
