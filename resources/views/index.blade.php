@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ $title }}</h1>

        @can('configure fields')
            <dropdown-list @if($canCreate) class="mr-2" @endif>
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $resource->handle()]) }}"></dropdown-item>
            </dropdown-list>
        @endcan

        @if($canCreate)
            <a
                class="btn-primary"
                href="{{ cp_route('runway.create', ['resource' => $resource->handle()]) }}"
            >
                {{ __('Create :resource', [
                    'resource' => $resource->singular()
                ]) }}
            </a>
        @endif
    </div>

    <runway-listing
        :filters="{{ $filters->toJson() }}"
        :listing-config='@json($listingConfig)'
        :initial-columns='@json($columns)'
        action-url="{{ $actionUrl }}"
        initial-primary-column="{{ $primaryColumn }}"
        :has-publish-states="{{ $str::bool($hasPublishStates) }}"
    ></runway-listing>
@endsection
