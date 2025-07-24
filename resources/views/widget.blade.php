@php
    use Statamic\Facades\Site;
    use function Statamic\trans as __;
@endphp

<runway-resource-widget
    resource="{{ $resource->handle() }}"
    icon="{{ $icon }}"
    title="{{ $title }}"
    :additional-columns="{{ $columns->toJson() }}"
    :filters="{{ $filters->toJson() }}"
    initial-sort-column="{{ $sortColumn }}"
    initial-sort-direction="{{ $sortDirection }}"
    :initial-per-page="{{ $limit }}"
    :has-publish-states="{{ Statamic\Support\Str::bool($hasPublishStates) }}"
    title-column="{{ $titleColumn }}"
>
    @if ($canCreate)
        <template #actions>
            <ui-button href="{{ cp_route('runway.index', $resource->handle()) }}">
                {{ __('View All') }}
            </ui-button>
        </template>
    @endif
</runway-resource-widget>
