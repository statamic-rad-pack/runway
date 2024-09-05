import PublishForm from './components/resources/PublishForm.vue';
import ResourceView from './components/resources/View.vue';
import RelatedItem from './components/inputs/relationship/Item.vue';

Statamic.$components.register('runway-publish-form', PublishForm);
Statamic.$components.register('resource-view', ResourceView);
Statamic.$components.register('runway-related-item', RelatedItem);
