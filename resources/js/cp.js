import PublishForm from './components/resources/PublishForm.vue';
import ResourceView from './components/resources/View.vue';
import RelatedItem from './components/inputs/relationship/Item.vue';

Statamic.booting(() => {
    Statamic.$components.register('runway-related-item', RelatedItem);
    Statamic.$components.register('runway-publish-form', PublishForm);
    Statamic.$components.register('runway-resource-view', ResourceView);
});
