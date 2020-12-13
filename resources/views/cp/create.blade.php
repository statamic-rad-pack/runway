@extends('statamic::layout')
@section('title', 'Create ' . $model['singular'])
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <publish-form
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        title="Create {{ $model['singular'] }}"
        name="create-publish-form"
        action="{{ $action }}"
        method="post"
    ></publish-form>
@endsection
