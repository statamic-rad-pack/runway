<template>
    <!--
        This component is *mostly* the same as the one in Statamic Core, however with one difference:
        it uses the RunwayPublishForm component instead of Statamic's EntryPublishForm.
    -->

    <div
        class="block cursor-pointer space-y-2 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900"
        :class="{
            'status-working-copy': revision.working,
            'status-published': revision.attributes.published,
        }"
        @click="open"
    >
        <div v-if="revision.message" class="revision-item-note truncate" v-text="revision.message" />

        <div class="flex items-center gap-2">
            <avatar v-if="revision.user" :user="revision.user" class="size-6 shrink-0" />

            <div class="revision-item-content flex w-full">
                <div class="flex-1">
                    <Subheading>
                        <template v-if="revision.user">
                            {{ revision.user.name || revision.user.email }} &ndash;
                        </template>
                        {{ time }}
                    </Subheading>
                </div>

                <div class="flex items-center gap-1">
                    <Badge
                        size="sm"
                        :color="
                            revision.working
                                ? 'gray'
                                : {
                                      publish: 'green',
                                      revision: 'gray',
                                      restore: 'gray',
                                      unpublish: 'red',
                                  }[revision.action]
                        "
                        :text="
                            revision.working
                                ? __('Working Copy')
                                : {
                                      publish: __('Published'),
                                      revision: __('Revision'),
                                      restore: __('Restored'),
                                      unpublish: __('Unpublished'),
                                  }[revision.action]
                        "
                    />
                    <Badge size="sm" color="orange" v-if="revision.attributes.current" v-text="__('Current')" />
                </div>

                <revision-preview
                    v-if="showDetails"
                    :revision="revision"
                    component="runway-publish-form"
                    :component-props="componentProps"
                    @closed="showDetails = false"
                >
                    <template slot="action-buttons-right">
                        <restore-revision
                            v-if="canRestoreRevisions"
                            :revision="revision"
                            :url="restoreUrl"
                            :reference="reference"
                            class="ltr:ml-4 rtl:mr-4"
                        />
                    </template>
                </revision-preview>
            </div>
        </div>
    </div>
</template>

<script>
import { DateFormatter } from '@statamic/cms';
import { RestoreRevision, RevisionPreview } from '@statamic/cms/temporary';
import { Subheading, Badge } from '@statamic/cms/ui';

export default {
    components: {
        RevisionPreview,
        RestoreRevision,
        Subheading,
        Badge,
    },

    props: {
        revision: Object,
        restoreUrl: String,
        reference: String,
        canRestoreRevisions: Boolean,
    },

    data() {
        return {
            showDetails: false,
            componentProps: {
                initialActions: 'actions',
                collectionTitle: 'collection.title',
                collectionUrl: 'collection.url',
                initialTitle: 'title',
                initialReference: 'reference',
                initialBlueprint: 'blueprint',
                initialValues: 'values',
                initialMeta: 'meta',
                initialPublished: 'published',
                initialPermalink: 'permalink',
                initialIsWorkingCopy: 'hasWorkingCopy',
                initialIsRoot: 'isRoot',
                initialReadOnly: 'readOnly',
                canManagePublishState: 'canManagePublishState',
                resourceHasRoutes: 'resourceHasRoutes',
                resource: 'resource',
            },
        };
    },

    computed: {
        time() {
            return DateFormatter.format(this.revision.date * 1000, 'time');
        },
    },

    methods: {
        open() {
            if (this.revision.working) {
                this.$emit('working-copy-selected');
                return;
            }

            this.showDetails = true;
        },
    },
};
</script>
