@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="widgets @container flex flex-wrap -mx-4 py-2">
        @foreach($widgets as $widget)
            <div class="widget w-full md:{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} mb-8 px-4">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ $title }}</h1>

        @if(! $resource->readOnly())
            @can('create', $resource)
                <a
                    class="btn-primary"
                    href="{{ cp_route('runway.create', ['resourceHandle' => $resource->handle()]) }}"
                >
                    {{ __('Create :resource', [
                        'resource' => $resource->singular()
                    ]) }}
                </a>
            @endcan
        @endif
    </div>

    <runway-listing
        :filters="{{ $filters->toJson() }}"
        :listing-config='@json($listingConfig)'
        :initial-columns='@json($columns)'
        action-url="{{ $actionUrl }}"
        initial-primary-column="{{ $primaryColumn }}"
    ></runway-listing>
@endsection
