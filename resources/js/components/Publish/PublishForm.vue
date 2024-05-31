<template>
    <div>
        <breadcrumb
            v-if="breadcrumbs"
            :url="breadcrumbs[0].url"
            :title="breadcrumbs[0].text"
        />

        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-html="title" />
                </div>
            </h1>

            <dropdown-list class="mr-4" v-if="canEditBlueprint">
                <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                <li class="divider" />
                <data-list-inline-actions
                    v-if="!isCreating"
                    :item="values.id"
                    :url="itemActionUrl"
                    :actions="itemActions"
                    :is-dirty="isDirty"
                    @started="actionStarted"
                    @completed="actionCompleted"
                />
            </dropdown-list>

            <div class="pt-px text-2xs text-gray-600 flex mr-4" v-if="readOnly">
                <svg-icon name="light/lock" class="w-4 mr-1 -mt-1" /> {{ __('Read Only') }}
            </div>

            <div class="hidden md:flex items-center">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!revisionsEnabled && !isInline"
                    :button-class="saveButtonClass"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        :class="saveButtonClass"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="saveText"
                    />
                </save-button-options>

                <button
                    v-if="revisionsEnabled && !isCreating"
                    class="rtl:mr-4 ltr:ml-4 btn-primary flex items-center"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true">
                    <span>{{ __('Publish') }}…</span>
                </button>
            </div>

            <slot name="action-buttons-right" />
        </div>

        <publish-container
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="blueprint"
            :values="values"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
        >
            <div>
                <component
                    v-for="component in components"
                    :key="component.id"
                    :is="component.name"
                    :container="container"
                    v-bind="component.props"
                    v-on="component.events"
                />

                <publish-tabs
                    :read-only="readOnly"
                    :enable-sidebar="shouldShowSidebar"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @focus="$refs.container.$emit('focus', $event)"
                    @blur="$refs.container.$emit('blur', $event)"
                >
                    <template #actions="{ shouldShowSidebar }">
                        <div class="card p-0 mb-5">
                            <div v-if="resourceHasRoutes && permalink" :class="{ 'hi': !shouldShowSidebar }">
                                <div class="p-3 flex items-center space-x-2">
                                    <a
                                        class="flex items-center justify-center btn w-full"
                                        v-if="permalink"
                                        :href="permalink"
                                        target="_blank"
                                    >
                                        <svg-icon name="light/external-link" class="w-4 h-4 rtl:ml-2 ltr:mr-2 shrink-0" />
                                        <span>{{ __('Visit URL') }}</span>
                                    </a>
                                </div>
                            </div>

                            <div
                                v-if="revisionsEnabled && !isCreating"
                                class="p-4"
                                :class="{ 'border-t dark:border-dark-900': resourceHasRoutes && permalink }"
                            >
                                <label class="publish-field-label font-medium mb-2" v-text="__('Revisions')"/>
<!--                                <div class="mb-1 flex items-center" v-if="published">-->
<!--                                    <span class="text-green-600 w-6 text-center">&check;</span>-->
<!--                                    <span class="text-2xs" v-text="__('Entry has a published version')"></span>-->
<!--                                </div>-->
<!--                                <div class="mb-1 flex items-center" v-else>-->
<!--                                    <span class="text-orange w-6 text-center">!</span>-->
<!--                                    <span class="text-2xs" v-text="__('Entry has not been published')"></span>-->
<!--                                </div>-->
                                <div class="mb-1 flex items-center" v-if="!isWorkingCopy && published">
                                    <span class="text-green-600 w-6 text-center">&check;</span>
                                    <span class="text-2xs" v-text="__('This is the published version')"></span>
                                </div>
                                <div class="mb-1 flex items-center" v-if="isDirty">
                                    <span class="text-orange w-6 text-center">!</span>
                                    <span class="text-2xs" v-text="__('Unsaved changes')"></span>
                                </div>
                                <button
                                    class="flex items-center justify-center mt-4 btn-flat px-2 w-full"
                                    v-if="!isCreating && revisionsEnabled"
                                    @click="showRevisionHistory = true">
                                    <svg-icon name="light/history" class="h-4 w-4 rtl:ml-2 ltr:mr-2" />
                                    <span>{{ __('View History') }}</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </publish-tabs>
            </div>

            <template v-slot:buttons>
                <button
                    v-if="!readOnly"
                    class="rtl:mr-4 ltr:ml-4"
                    :class="{
                        'btn': revisionsEnabled,
                        'btn-primary': isCreating || !revisionsEnabled,
                    }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="saveText">
                </button>

                <button
                    v-if="revisionsEnabled && !isCreating"
                    class="rtl:mr-4 ltr:ml-4 btn-primary flex items-center"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true">
                    <span v-text="__('Publish')" />
                    <svg-icon name="micro/chevron-down-xs" class="rtl:mr-2 ltr:ml-2 w-2" />
                </button>
            </template>
        </publish-container>

        <div class="md:hidden mt-6 flex items-center">
            <button
                v-if="!readOnly"
                class="btn-lg"
                :class="{
                    'btn-primary w-full': ! revisionsEnabled,
                    'btn w-1/2 rtl:ml-4 ltr:mr-4': revisionsEnabled,
                }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__(revisionsEnabled ? 'Save Changes' : 'Save')" />

            <button
                v-if="revisionsEnabled"
                class="rtl:mr-2 ltr:ml-2 btn btn-lg justify-center btn-primary flex items-center w-1/2"
                :disabled="!canPublish"
                @click="confirmingPublish = true">
                <span v-text="__('Publish')" />
                <svg-icon name="micro/chevron-down-xs" class="rtl:mr-2 ltr:ml-2 w-2" />
            </button>
        </div>

        <stack name="revision-history" v-if="showRevisionHistory" @closed="showRevisionHistory = false" :narrow="true">
            <revision-history
                slot-scope="{ close }"
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                :reference="initialReference"
                @closed="close"
            />
        </stack>

<!--        todo: publish-actions?-->
    </div>
</template>

<script>
import SaveButtonOptions from '../../../../vendor/statamic/cms/resources/js/components/publish/SaveButtonOptions.vue'
import HasPreferences from '../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences'
import HasHiddenFields from '../../../../vendor/statamic/cms/resources/js/components/publish/HasHiddenFields.js'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions.js'
import RevisionHistory from '../../../../vendor/statamic/cms/resources/js/components/revision-history/History.vue'

export default {
    components: {
        SaveButtonOptions,
        RevisionHistory,
    },

    mixins: [HasPreferences, HasHiddenFields, HasActions],

    props: {
        breadcrumbs: Array,
        initialReference: String,
        initialActions: Object,
        initialBlueprint: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        method: String,
        resourceHasRoutes: Boolean,
        permalink: String,
        isCreating: {
            type: Boolean,
            default: false,
        },
        isInline: {
            type: Boolean,
            default: false,
        },
        publishContainer: String,
        readOnly: Boolean,
        resource: {
            type: Object,
            required: true,
        },
        createAnotherUrl: String,
        listingUrl: String,
        canEditBlueprint: Boolean,
        revisionsEnabled: Boolean,
        canPublish: Boolean,

        initialIsWorkingCopy: Boolean,
    },

    data() {
        return {
            actions: this.initialActions,
            blueprint: this.initialBlueprint,
            values: this.initialValues,
            meta: this.initialMeta,
            title: this.initialTitle,
            preferencesPrefix: `runway.${this.resource.handle}`,

            errors: {},
            saving: false,
            containerWidth: null,
            saveKeyBinding: null,
            quickSave: false,

            showRevisionHistory: false,
            confirmingPublish: false, // what does this do? do we need it?
            isWorkingCopy: this.initialIsWorkingCopy,

            // Whether it was published the last time it was saved.
            // Successful publish actions (if using revisions) or just saving (if not) will update this.
            // The current published value is inside the "values" object, and also accessible as a computed.
            initialPublished: this.initialValues.published,
        }
    },

    computed: {
        enableSidebar() {
            return this.blueprint.tabs
                .map((section) => section.handle)
                .includes('sidebar')
        },

        canSave() {
            return !this.readOnly;
        },

        saveText() {
            switch(true) {
                case this.revisionsEnabled:
                    return __('Save Changes');
                // case this.isUnpublishing:
                //     return __('Save & Unpublish');
                // case this.isDraft:
                //     return __('Save Draft');
                default:
                    return __('Save & Publish');
            }
        },

        shouldShowSidebar() {
            return this.enableSidebar
        },

        published() {
          return this.values.published;
        },

        isUnpublishing() {
            return this.initialPublished && ! this.published && ! this.isCreating;
        },

        isDraft() {
            return ! this.published;
        },

        saveButtonClass() {
            return {
                'btn': this.revisionsEnabled,
                'btn-primary': this.isCreating || ! this.revisionsEnabled,
            };
        },

        afterSaveOption() {
            return this.getPreference('after_save')
        },
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(
            ['mod+s', 'mod+return'],
            (e) => {
                e.preventDefault()
                this.quickSave = true
                this.save()
            }
        )
    },

    created() {
        if (this.publishContainer.includes('relate-fieldtype-inline')) {
            this.prefillBelongsToField()
        }
    },

    methods: {
        save() {
            if (! this.canSave) {
                this.quickSave = false;
                return;
            }

            this.saving = true;
            this.clearErrors();

            setTimeout(() => this.runBeforeSaveHook(), 151);  // 150ms is the debounce time for fieldtype updates
        },

        runBeforeSaveHook() {
            this.$refs.container.saving();

            Statamic.$hooks.run('model.saving', {
                resource: window.Runway.currentResource,
                values: this.values,
                container: this.$refs.container,
                storeName: this.publishContainer,
            })
                .then(this.performSaveRequest)
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(error || 'Something went wrong');
                });
        },

        performSaveRequest() {
            // Once the hook has completed, we need to make the actual request.
            // We build the payload here because the before hook may have modified values.
            // const payload = this.visibleValues;

            const payload = this.values;

            this.$axios({
                method: this.method,
                url: this.actions.save,
                data: payload,
            }).then(response => {
                this.saving = false;
                if (! response.data.saved) {
                    return this.$toast.error(__(`Couldn't save entry`));
                }
                // this.title = response.data.data.title;
                this.isWorkingCopy = true;
                if (!this.revisionsEnabled) this.permalink = response.data.data.permalink;
                if (!this.isCreating && !this.isAutosave) this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.runAfterSaveHook(response);
            }).catch(error => this.handleAxiosError(error));
        },

        runAfterSaveHook(response) {
            // Once the save request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('model.saved', {
                    resource: window.Runway.currentResource,
                    reference: this.initialReference,
                    response
                })
                .then(() => {
                    // If revisions are enabled, just emit event.
                    if (this.revisionsEnabled) {
                        clearTimeout(this.trackDirtyStateTimeout)
                        this.trackDirtyState = false
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350)
                        this.$nextTick(() => this.$emit('saved', response));
                        return;
                    }

                    let nextAction = this.quickSave || this.isAutosave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        window.location = this.createAnotherUrl;
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    else if (!this.isInline && nextAction === null) {
                        window.location = this.listingUrl;
                    }

                    // Otherwise, leave them on the edit form and emit an event. We need to wait until after
                    // the hooks are resolved because if this form is being shown in a stack, we only
                    // want to close it once everything's done.
                    else {
                        // todo: copied from old save implementation, evaluate if it's still needed
                        if (this.isCreating && this.publishContainer === 'base') {
                            window.location = response.data.redirect
                        }

                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350);
                        // this.initialPublished = response.data.data.published;
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                    this.isAutosave = false;
                }).catch(e => console.error(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
                this.$reveal.invalid();
            } else if (e.response) {
                this.$toast.error(e.response.data.message);
            } else {
                this.$toast.error(e || 'Something went wrong');
            }
        },

        clearErrors() {
            this.error = null
            this.errors = {}
        },

        setFieldValue(handle, value) {
            this.$refs.container.setFieldValue(handle, value)
        },

        setFieldMeta(handle, value) {
            this.$store.dispatch(
                `publish/${this.publishContainer}/setFieldMeta`,
                {
                    handle,
                    value,
                    user: Statamic.user.id,
                }
            )
        },

        /**
         * When creating a new model via the HasMany fieldtype, pre-fill the belongs_to field to the current model.
         */
        prefillBelongsToField() {
            this.values['from_inline_publish_form'] = true

            this.initialBlueprint.tabs.forEach((tab) => {
                tab.sections.forEach((section) => {
                    section.fields
                        .filter((field) => {
                            return field.type === 'belongs_to' && field.resource === window.Runway.currentResource;
                        })
                        .forEach((field) => {
                            let alreadyExists = this.values[field.handle].includes(window.Runway.currentModel.id)

                            if (!alreadyExists) {
                                this.values[field.handle].push(window.Runway.currentModel.id)
                                this.meta[field.handle].data = [window.Runway.currentModel]
                            }
                        })
                })
            })
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                if (!this.revisionsEnabled) this.permalink = response.data.permalink;
                this.values = this.resetValuesFromResponse(response.data.values);
                this.itemActions = response.data.itemActions;
            }
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`runway-publish-form`, saving)
        },
    },

    destroyed() {
        this.saveKeyBinding.destroy()
    },
}
</script>
