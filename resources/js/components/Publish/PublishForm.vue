<template>
  <div>
    <breadcrumb
      v-if="breadcrumbs"
      :url="breadcrumbs[0].url"
      :title="breadcrumbs[0].text"
    />

    <div class="flex items-center mb-3">
      <h1 class="flex-1">
        <div class="flex items-center">
          <span v-html="title" />
        </div>
      </h1>

      <div class="hidden md:flex items-center">
        <button class="btn-primary" :disabled="isSaving" @click.prevent="save">
          Save
        </button>
      </div>
    </div>

    <publish-container
      ref="container"
      :name="publishContainer"
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

        <publish-sections
          :read-only="readOnly"
          :enable-sidebar="shouldShowSidebar"
          @updated="setFieldValue"
          @meta-updated="setFieldMeta"
          @focus="$refs.container.$emit('focus', $event)"
          @blur="$refs.container.$emit('blur', $event)"
        >
          <template #actions="{ shouldShowSidebar }">
            <div
              v-if="resourceHasRoutes && permalink"
              :class="{ hi: !shouldShowSidebar }"
            >
              <div class="p-2 flex items-center -mx-1">
                <a
                  class="flex items-center justify-center btn-flat w-full mx-1 px-1"
                  v-if="permalink"
                  :href="permalink"
                  target="_blank"
                >
                  <svg-icon name="external-link" class="w-5 h-5 mr-1" />
                  <span>{{ __("Visit URL") }}</span>
                </a>
              </div>
            </div>
          </template>
        </publish-sections>
      </div>
    </publish-container>
  </div>
</template>

<script>
export default {
  props: {
    breadcrumbs: Array,
    initialBlueprint: Object,
    initialValues: Object,
    initialMeta: Object,
    initialTitle: String,
    action: String,
    method: String,
    resourceHasRoutes: Boolean,
    permalink: String,
    isCreating: Boolean,
    publishContainer: String,
  },

  data() {
    return {
      blueprint: this.initialBlueprint,
      values: this.initialValues,
      meta: this.initialMeta,
      title: this.initialTitle,

      readonly: false, // TODO: might do this in the future

      errors: {},
      saving: false,
      containerWidth: null,
      saveKeyBinding: null,
    };
  },

  computed: {
    enableSidebar() {
      return this.blueprint.sections
        .map((section) => section.handle)
        .includes("sidebar");
    },

    shouldShowSidebar() {
      return this.enableSidebar;

      // return this.enableSidebar && this.containerWidth > 920
    },
  },

  created() {
    console.log("mount", this.publishContainer);

    // If we're creating a resource through the 'Create' on a HasMany field somewhere, fill any fields...
    if (this.publishContainer.includes("relate-fieldtype-inline")) {
      console.log("test 1");

      let baseContainer = this.$store.state.publish.base;

      console.log("test 2", baseContainer);

      let resourceHandle = baseContainer.blueprint.handle; // TODO: this could be better.. what if the blueprint handle isn't the same as the resource handle?

      console.log("test 3", resourceHandle);

      this.initialBlueprint.sections.forEach((section) => {
        console.log("test 4", section);

        section.fields.forEach((field) => {
          console.log("test 5", field);

          if (
            field.type === "belongs_to" &&
            field.resource === resourceHandle
          ) {
            console.log(
              "test 6",
              field,
              window.location.pathname.split("/").reverse()[0]
            );

            this.values[field.handle].push(
              window.location.pathname.split("/").reverse()[0]
            ); // TODO: just a prototype right now
          }
        });
      });

      // get resource name of base container

      // do we have any fields in this blueprint with that resource?
      // if yes, set the value of that field to the base ID (take into account belongs to & has many stuff)
    }
  },

  methods: {
    save() {
      this.saving = true;
      this.clearErrors();

      this.$axios({
        method: this.method,
        url: this.action,
        data: this.values,
      })
        .then((response) => {
          this.saving = false;
          this.$refs.container.saved();
          this.$emit("saved", response);

          if (this.isCreating && this.publishContainer === "base") {
            this.$nextTick(() => {
              window.location.href = response.data.redirect;
            });
          } else {
            this.$toast.success(__("Saved"));
          }
        })
        .catch((error) => this.handleAxiosError(error));
    },

    clearErrors() {
      this.error = null;
      this.errors = {};
    },

    handleAxiosError(e) {
      this.saving = false;

      if (e.response && e.response.status === 422) {
        const { message, errors } = e.response.data;
        this.error = message;
        this.errors = errors;
        this.$toast.error(message);
      } else if (e.response) {
        this.$toast.error(e.response.data.message);
      } else {
        this.$toast.error(e || "Something went wrong");
      }
    },

    setFieldValue(handle, value) {
      this.$refs.container.setFieldValue(handle, value);
    },

    setFieldMeta(handle, value) {
      this.$store.dispatch(`publish/${this.publishContainer}/setFieldMeta`, {
        handle,
        value,
        user: Statamic.user.id,
      });
    },
  },

  watch: {
    saving(saving) {
      this.$progress.loading(`runway-publish-form`, saving);
    },
  },

  mounted() {
    this.saveKeyBinding = this.$keys.bindGlobal(
      ["mod+s", "mod+return"],
      (e) => {
        e.preventDefault();
        this.save();
      }
    );
  },

  destroyed() {
    this.saveKeyBinding.destroy();
  },
};
</script>
