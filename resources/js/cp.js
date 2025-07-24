import RelatedItem from './components/inputs/relationship/Item.vue';
import BaseCreateForm from './components/resources/BaseCreateForm.vue';
import PublishForm from './components/resources/PublishForm.vue';
import ResourceView from './components/resources/View.vue';
import ResourceWidget from './components/resources/Widget.vue';

Statamic.booting(() => {
    Statamic.$components.register('runway-related-item', RelatedItem);
    Statamic.$components.register('runway-base-create-form', BaseCreateForm);
    Statamic.$components.register('runway-publish-form', PublishForm);
    Statamic.$components.register('runway-resource-view', ResourceView);
    Statamic.$components.register('runway-resource-widget', ResourceWidget);
});
