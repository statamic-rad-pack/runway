<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            class="mb-4"
            :visible-columns="columns"
            :columns="columns"
            :rows="items"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card p-0 relative">
                    <data-list-filter-presets
                        ref="presets"
                        :active-preset="activePreset"
                        :preferences-prefix="preferencesPrefix"
                        @selected="selectPreset"
                        @reset="filtersReset"
                    />

                    <div class="data-list-header">
                        <data-list-filters
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @filter-changed="filterChanged"
                            @search-changed="searchChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                            @restore-preset="$refs.presets.viewPreset($event)"
                            @reset="filtersReset"
                        />
                    </div>

                    <div
                        v-show="items.length === 0"
                        class="p-3 text-center text-grey-50"
                        v-text="__('No results')"
                    />

                    <data-list-table
                        v-show="items.length"
                        :allow-bulk-actions="false"
                        :loading="loading"
                        :reorderable="false"
                        :sortable="true"
                        :toggle-selection-on-row-click="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                    >
                        <template slot="cell-label" slot-scope="{ row }">
                            <a
                                :href="cp_url(listingConfig.editUrl + row.id)"
                            >{{ row.label }}</a>
                        </template>

                        <template slot="actions" slot-scope="{ row, index }">
                            <dropdown-list>
                                <dropdown-item
                                    :text="__('Edit')"
                                    :redirect="cp_url(listingConfig.editUrl + row.id)"
                                />

                                <dropdown-item
                                    :text="__('Delete')"
                                    class="warning"
                                    @click="confirmDeleteRow(row.id, index)"
                                    v-if="listingConfig.deleteUrl !== undefined"
                                />
                            </dropdown-list>

                            <confirmation-modal
                                v-if="deletingRow !== false"
                                :title="__('Delete')"
                                :bodyText="__('Are you sure you want to delete this item?')"
                                :buttonText="__('Delete')"
                                :danger="true"
                                @confirm="deleteRow(listingConfig.listingUrl)"
                                @cancel="cancelDeleteRow"
                            ></confirmation-modal>
                        </template>
                    </data-list-table>
                </div>
            </div>
        </data-list>

        <data-list-pagination
            class="mt-3"
            :resource-meta="meta"
            :per-page="perPage"
            @page-selected="selectPage"
            @per-page-changed="changePerPage"
        />
    </div>
</template>

<script>
import DeletesListingRow from "./DeletesListingRow.js";
import Listing from '../../../../vendor/statamic/cms/resources/js/components/Listing.vue';

export default {
  mixins: [Listing, DeletesListingRow],

  props: {
    listingConfig: Array,
  },

  data() {
    return {
      listingKey: "id",
      preferencesPrefix: this.listingConfig.preferencesPrefix ?? "runway",
      requestUrl: this.listingConfig.requestUrl,
      columns: this.columns,
      meta: {},
    };
  },
}
</script>
