<template>
    <div>
        <Header>
            <template #title>
                <StatusIndicator v-if="publishStatesEnabled && !isCreating" :status />
                {{ formattedTitle }}
            </template>

            <ItemActions
                v-if="!isCreating && hasItemActions"
                :item="values.id"
                :url="itemActionUrl"
                :actions="itemActions"
                :is-dirty="isDirty"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions: itemActions }"
            >
                <Dropdown v-if="canEditBlueprint || hasItemActions">
                    <template #trigger>
                        <Button icon="dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            :text="__('Edit Blueprint')"
                            icon="blueprint-edit"
                            v-if="canEditBlueprint"
                            :href="actions.editBlueprint"
                        />
                        <DropdownSeparator v-if="canEditBlueprint && itemActions.length" />
                        <DropdownItem
                            v-for="action in itemActions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>

            <ui-badge icon="padlock-locked" :text="__('Read Only')" v-if="readOnly" />

            <div class="flex items-center gap-3">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!revisionsEnabled && !isInline"
                    :preferences-prefix="preferencesPrefix"
                >
                    <Button
                        :disabled="!canSave"
                        :variant="!revisionsEnabled ? 'primary' : 'default'"
                        @click.prevent="save"
                        v-text="saveText"
                    />
                </save-button-options>

                <save-button-options
                    v-if="revisionsEnabled && !isCreating"
                    :show-options="!isInline"
                    :preferences-prefix="preferencesPrefix"
                >
                    <Button
                        variant="primary"
                        :disabled="!canPublish"
                        @click="confirmingPublish = true"
                        :text="publishButtonText"
                    />
                </save-button-options>
            </div>

            <slot name="action-buttons-right" />
        </Header>

        <PublishContainer
            v-if="blueprint"
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="blueprint"
            v-model="values"
            :meta="meta"
            :errors="errors"
            :track-dirty-state="trackDirtyState"
            :remember-tab="!isInline"
        >
            <LivePreview
                :enabled="isPreviewing"
                :url="livePreviewUrl"
                :targets="previewTargets"
                @opened="openLivePreview"
                @closed="closeLivePreview"
            >
                <PublishComponents />

                <PublishTabs>
                    <template v-if="resourceHasRoutes || publishStatesEnabled || revisionsEnabled" #actions>
                        <div class="space-y-6">
                            <!-- Visit URL Buttons -->
                            <div v-if="resourceHasRoutes">
                                <div class="flex flex-wrap gap-4" v-if="showLivePreviewButton || showVisitUrlButton">
                                    <Button
                                        :text="__('Live Preview')"
                                        class="flex-1"
                                        icon="live-preview"
                                        @click="openLivePreview"
                                        v-if="showLivePreviewButton"
                                    />
                                    <Button
                                        :href="permalink"
                                        :text="__('Visit URL')"
                                        class="flex-1"
                                        icon="external-link"
                                        target="_blank"
                                        v-if="showVisitUrlButton"
                                    />
                                </div>
                            </div>

                            <!-- Published Switch -->
                            <Panel v-if="publishStatesEnabled && !revisionsEnabled" class="flex justify-between px-5 py-3">
                                <Heading :text="__('Published')" />
                                <Switch
                                    :model-value="published"
                                    :read-only="!canManagePublishState"
                                    @update:model-value="setFieldValue('published', $event)"
                                />
                            </Panel>

                            <!-- Revisions -->
                            <Panel v-if="revisionsEnabled && !isCreating">
                                <PanelHeader class="flex items-center justify-between">
                                    <Heading :text="__('Revisions')" />
                                    <Button
                                        @click="showRevisionHistory = true"
                                        icon="history"
                                        :text="__('View History')"
                                        size="xs"
                                        class="-me-4"
                                    />
                                </PanelHeader>
                                <Card class="space-y-2">
                                    <Subheading v-if="published" class="flex items-center gap-2">
                                        <Icon name="checkmark" class="text-green-600" />
                                        {{ __('Model has a published version') }}
                                    </Subheading>
                                    <Subheading v-else class="flex items-center gap-2 text-yellow-600">
                                        <Icon name="warning-diamond" />
                                        {{ __('Model has not been published') }}
                                    </Subheading>
                                    <Subheading v-if="!isWorkingCopy && published" class="flex items-center gap-2">
                                        <Icon name="checkmark" class="text-green-600" />
                                        {{ __('This is the published version') }}
                                    </Subheading>
                                    <Subheading v-if="isDirty" class="flex items-center gap-2 text-yellow-600">
                                        <Icon name="warning-diamond" />
                                        {{ __('Unsaved changes') }}
                                    </Subheading>
                                </Card>
                            </Panel>
                        </div>
                    </template>
                </PublishTabs>

                <template #buttons>
                    <Button
                        v-if="!readOnly"
                        size="sm"
                        :variant="revisionsEnabled ? 'default' : 'primary'"
                        :disabled="!canSave"
                        @click.prevent="save"
                        :text="saveText"
                    ></Button>

                    <Button
                        v-if="revisionsEnabled"
                        size="sm"
                        variant="primary"
                        :disabled="!canPublish"
                        @click="confirmingPublish = true"
                        :text="publishButtonText"
                    />
                </template>
            </LivePreview>
        </PublishContainer>

        <stack
            name="revision-history"
            v-if="showRevisionHistory"
            @closed="showRevisionHistory = false"
            :narrow="true"
            v-slot="{ close }"
        >
            <revision-history
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                :reference="initialReference"
                :can-restore-revisions="!readOnly"
                @closed="close"
            />
        </stack>

        <publish-actions
            v-if="confirmingPublish"
            :actions="actions"
            :published="published"
            :collection="collectionHandle"
            :reference="initialReference"
            :publish-container="publishContainer"
            :can-manage-publish-state="canManagePublishState"
            @closed="confirmingPublish = false"
            @saving="saving = true"
            @saved="publishActionCompleted"
            @failed="publishActionFailed"
        />
    </div>
</template>

<script>
import { router } from '@statamic/cms/inertia';
import {
    ItemActions,
    SaveButtonOptions,
    HasPreferencesMixin,
    HasActionsMixin,
    clone,
    resetValuesFromResponse
} from '@statamic/cms';
import {
    Button,
    Card,
    CardPanel,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    DropdownSeparator,
    Header,
    Heading,
    Icon,
    Panel,
    PanelHeader,
    StatusIndicator,
    Subheading,
    Switch,
    Select,
    PublishContainer,
    PublishTabs,
    PublishComponents,
    PublishLocalizations as LocalizationsCard,
    LivePreview,
    publishContextKey,
} from '@statamic/cms/ui';
import { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } from '@statamic/cms/save-pipeline';
import PublishActions from './PublishActions.vue';
import RevisionHistory from './revision-history/History.vue';
import striptags from 'striptags';
import { computed, ref } from 'vue';

export default {
    mixins: [HasPreferencesMixin, HasActionsMixin],

    components: {
        Button,
        Card,
        CardPanel,
        Dropdown,
        DropdownItem,
        DropdownMenu,
        DropdownSeparator,
        Header,
        Heading,
        Icon,
        ItemActions,
        LivePreview,
        LocalizationsCard,
        Panel,
        PanelHeader,
        PublishActions,
        PublishComponents,
        PublishContainer,
        PublishTabs,
        RevisionHistory,
        SaveButtonOptions,
        StatusIndicator,
        Subheading,
        Switch,
        Select,
    },

    inject: {
        publishContext: { from: publishContextKey }
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialBlueprint: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialIsWorkingCopy: Boolean,
        resource: Object,
        initialActions: Object,
        method: String,
        isCreating: Boolean,
        isInline: Boolean,
        initialReadOnly: Boolean,
        initialStatus: String,
        initialPermalink: String,
        revisionsEnabled: Boolean,
        canEditBlueprint: Boolean,
        canManagePublishState: Boolean,
        createAnotherUrl: String,
        initialListingUrl: String,
        resourceHasRoutes: Boolean,
        livePreviewUrl: String,
        previewTargets: Array,
    },

    data() {
        return {
            actions: this.initialActions,
            trackDirtyState: true,
            blueprint: this.initialBlueprint,
            title: this.initialTitle,
            status: this.initialStatus,
            values: clone(this.initialValues),
            visibleValues: {},
            meta: clone(this.initialMeta),
            isWorkingCopy: this.initialIsWorkingCopy,
            isPreviewing: false,
            tabsVisible: true,
            state: 'new',
            revisionMessage: null,
            showRevisionHistory: null,
            preferencesPrefix: `runway.${this.resource.handle}`,

            // Whether it was published the last time it was saved.
            // Successful publish actions (if using revisions) or just saving (if not) will update this.
            // The current published value is inside the "values" object, and also accessible as a computed.
            initialPublished: this.initialValues[this.resource.published_column],

            confirmingPublish: false,
            readOnly: this.initialReadOnly,
            permalink: this.initialPermalink,

            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
        };
    },

    setup() {
        const savingRef = ref(false);
        const errorsRef = ref({});

        return {
            savingRef: computed(() => savingRef),
            errorsRef: computed(() => errorsRef),
        };
    },

    computed: {
        containerRef() {
            return computed(() => this.$refs.container);
        },

        saving() {
            return this.savingRef.value;
        },

        errors() {
            return this.errorsRef.value;
        },

        formattedTitle() {
            return striptags(__(this.title));
        },

        somethingIsLoading() {
            return !this.$progress.isComplete();
        },

        canSave() {
            return !this.readOnly && !this.somethingIsLoading;
        },

        canPublish() {
            if (!this.revisionsEnabled) return false;

            if (this.readOnly || this.isCreating || this.somethingIsLoading || this.isDirty) return false;

            return true;
        },

        publishStatesEnabled() {
            return this.resource.has_publish_states;
        },

        published() {
            if (!this.publishStatesEnabled) return false;

            return this.values[this.resource.published_column];
        },

        listingUrl() {
            return `${this.initialListingUrl}`;
        },

        showLivePreviewButton() {
            return !this.isCreating && this.isBase && this.livePreviewUrl;
        },

        showVisitUrlButton() {
            return !!this.permalink;
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        publishButtonText() {
            if (this.canManagePublishState) {
                return `${__('Publish')}…`;
            }

            return `${__('Create Revision')}…`;
        },

        saveText() {
            switch (true) {
                case this.revisionsEnabled:
                    return __('Save Changes');
                case this.isUnpublishing:
                    return __('Save & Unpublish');
                case this.publishStatesEnabled && this.isDraft:
                    return __('Save Draft');
                default:
                    return this.publishStatesEnabled ? __('Save & Publish') : __('Save');
            }
        },

        isUnpublishing() {
            if (!this.publishStatesEnabled) return false;

            return this.initialPublished && !this.published && !this.isCreating;
        },

        isDraft() {
            if (!this.publishStatesEnabled) return false;

            return !this.published;
        },

        afterSaveOption() {
            return this.getPreference('after_save');
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },

        baseContainer() {
            let parentContainer = this.publishContext;

            while (parentContainer?.parentContainer) {
                parentContainer = parentContainer.parentContainer;
            }

            return parentContainer;
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-runway-publish-form`, saving);
        },

        title(title) {
            if (this.isBase) {
                const arrow = this.direction === 'ltr' ? '‹' : '›';
                const parts = document.title.split(arrow);

                document.title = `${title} ${arrow} ${parts[1]?.trim()}`;
            }
        },
    },

    methods: {
        save() {
            if (!this.canSave) {
                this.quickSave = false;
                return;
            }

            new Pipeline()
                .provide({
                    container: this.containerRef,
                    errors: this.errorsRef,
                    saving: this.savingRef,
                })
                .through([
                    new BeforeSaveHooks('runway', {
                        resource: this.resource,
                        values: this.values,
                    }),
                    new Request(this.actions.save, this.method),
                    new AfterSaveHooks('runway', {
                        resource: this.resource,
                        reference: this.initialReference,
                    }),
                ])
                .then((response) => {
                    // If revisions are enabled, just emit event.
                    if (this.revisionsEnabled) {
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.values = resetValuesFromResponse(response.data.data.values, this.$refs.container);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                        this.$nextTick(() => this.$emit('saved', response));
                        return;
                    }

                    this.title = response.data.data.title;
                    this.status = response.data.data.status;
                    this.isWorkingCopy = true;
                    if (!this.revisionsEnabled) this.permalink = response.data.data.permalink;
                    if (!this.isCreating) this.$toast.success(__('Saved'));

                    let nextAction = this.quickSave || this.afterSaveOption;

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        this.redirectTo(this.createAnotherUrl);
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    else if (!this.isInline && nextAction === null) {
                        this.redirectTo(this.listingUrl);
                    }

                    // Otherwise, leave them on the edit form and emit an event. We need to wait until after
                    // the hooks are resolved because if this form is being shown in a stack, we only
                    // want to close it once everything's done.
                    else {
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                        this.initialPublished = response.data.data.published;
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                })
                .catch((e) => {
                    if (!(e instanceof PipelineStopped)) {
                        this.$toast.error(__('Something went wrong'));
                        console.error(e);
                    }
                });
        },

        confirmPublish() {
            if (this.canPublish) {
                this.confirmingPublish = true;
            }
        },

        openLivePreview() {
            this.tabsVisible = false;
            this.$wait(200)
                .then(() => {
                    this.isPreviewing = true;
                    return this.$wait(300);
                })
                .then(() => (this.tabsVisible = true));
        },

        closeLivePreview() {
            this.isPreviewing = false;
            this.tabsVisible = true;
        },

        publishActionCompleted({ published, isWorkingCopy, response }) {
            this.saving = false;
            if (published !== undefined) {
                this.$refs.container.setFieldValue('published', published);
                this.initialPublished = published;
            }
            this.$refs.container.saved();
            this.isWorkingCopy = isWorkingCopy;
            this.confirmingPublish = false;
            this.title = response.data.data.title;
            this.status = response.data.data.status;
            clearTimeout(this.trackDirtyStateTimeout);
            this.trackDirtyState = false;
            this.values = this.resetValuesFromResponse(response.data.data.values);
            this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350);
            this.permalink = response.data.data.permalink;
            this.$nextTick(() => this.$emit('saved', response));
        },

        publishActionFailed() {
            this.confirmPublish = false;
            this.saving = false;
        },

        setFieldValue(handle, value) {
            this.$refs.container.setFieldValue(handle, value);
        },

        /**
         * Automatically populates the belongs_to relationship when creating a new model via the relationship fieldtype.
         * This method is called when the inline publish form is created.
         */
        populateBelongsToRelationship() {
            if (! this.baseContainer) return;

            this.initialBlueprint.tabs.forEach((tab) => {
                tab.sections.forEach((section) => {
                    section.fields
                        .filter((field) => field.type === 'belongs_to')
                        .filter((field) => {
                            // Gets the handle of the base resource from the store reference
                            // Example: "runway::posts::123" -> "posts"
                            let baseResource = this.baseContainer.reference.value.split('::')[1];

                            return field.resource === baseResource;
                        })
                        .forEach((field) => {
                            let alreadyExists = this.values[field.handle].includes(this.baseContainer.values.value.id);

                            if (!alreadyExists) {
                                this.values[field.handle].push(this.baseContainer.values.value.id);

                                this.meta[field.handle].data = [
                                    {
                                        id: this.baseContainer.values.value.id,
                                        reference: this.baseContainer.reference.value,
                                        title: this.baseContainer.values.value.title,
                                        edit_url: this.baseContainer.values.value.edit_url,
                                    },
                                ];
                            }
                        });
                });
            });
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                if (!this.revisionsEnabled) this.permalink = response.data.permalink;
                clearTimeout(this.trackDirtyStateTimeout);
                this.trackDirtyState = false;
                this.values = this.resetValuesFromResponse(response.data.values);
                this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350);
                if (this.publishStatesEnabled) {
                    this.initialPublished = response.data.published;
                }
                this.itemActions = response.data.itemActions;
            }
        },

        redirectTo(location) {
            router.get(location);
        }
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], (e) => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.quickSave = true;
            this.save();
        });

        if (!this.isRoot) {
            this.populateBelongsToRelationship();
        }
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));
    },

    unmounted() {
        clearTimeout(this.trackDirtyStateTimeout);
    },

    destroyed() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    },
};
</script>
