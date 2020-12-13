@extends('statamic::layout')
@section('title', $title)

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">{{ $title }}</h1>

        <a class="btn-primary" href="{{ cp_route('runway.create', ['model' => $model['_handle']]) }}">Create {{ $model['singular'] }}</a>
    </div>

    @if ($records->count())
        <div class="card p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column['title'] }}</th>
                        @endforeach
                        <th class="actions-column"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($records as $record)
                        <tr>
                            @foreach ($columns as $column)
                                <td>
                                    @if($column['has_link'])
                                        <div class="flex items-center">
                                            <a href="{{ cp_route('runway.edit', ['model' => $model['_handle'], 'record' => $record->id]) }}">{{ $record->{$column['handle']} }}</a>
                                        </div>
                                    @else
                                        {{ $record->{$column['handle']} }}
                                    @endif
                                </td>
                            @endforeach

                            <td class="flex justify-end">
                                <dropdown-list>
                                    <dropdown-item text="Edit" redirect="{{ cp_route('runway.edit', ['model' => $model['_handle'], 'record' => $record->id]) }}"></dropdown-item>
                                    <dropdown-item text="Delete" redirect="#"></dropdown-item>
                                </dropdown-list>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($records->hasMorePages())
                <div class="w-full flex mt-3">
                    <div class="flex-1"></div>

                    <ul class="flex justify-center items-center list-reset">
                        @if($records->previousPageUrl())
                            <li class="mx-1">
                                <a href="{{ $records->previousPageUrl() }}"><span>&laquo;</span></a>
                            </li>
                        @endif

                        @foreach($records->links()->elements[0] as $number => $link)
                            <li class="mx-1 @if($number === $records->currentPage()) font-bold @endif">
                                <a href="{{ $link }}">{{ $number }}</a>
                            </li>
                        @endforeach

                        @if($records->nextPageUrl())
                            <li class="mx-1">
                                <a href="{{ $records->nextPageUrl() }}">
                                    <span>»</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="flex flex-1">
                        <div class="flex-1"></div>
                    </div>
                </div>
            @endif
        </div>
    @else
        @include('statamic::partials.create-first', [
            'resource' => $title,
            'svg' => 'empty/collection',
            'route' => '#'
        ])
    @endif
@endsection
