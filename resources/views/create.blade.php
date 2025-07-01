@use(Statamic\CP\Breadcrumbs\Breadcrumbs)
@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Breadcrumbs::title($title))

@section('content')
    <runway-base-create-form
        :actions="{{ json_encode($actions) }}"
        :resource="{{ json_encode($resource->toArray()) }}"
        :resource-has-routes="{{ $str::bool($resourceHasRoutes) }}"
        create-label="{{ __('Create :resource', ['resource' => $resource->singular()]) }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
        create-another-url="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
        initial-listing-url="{{ cp_route('runway.index', ['resource' => $resource->handle()]) }}"
    ></runway-base-create-form>
@endsection
