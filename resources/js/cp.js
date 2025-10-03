import RelatedItem from './components/inputs/relationship/Item.vue';
import PublishForm from './components/resources/PublishForm.vue';
import ResourceWidget from './components/resources/Widget.vue';

import Index from './pages/Index.vue';
import Create from './pages/Create.vue';
import Edit from './pages/Edit.vue';

Statamic.booting(() => {
    Statamic.$components.register('runway-related-item', RelatedItem);
    Statamic.$components.register('runway-publish-form', PublishForm);
    Statamic.$components.register('runway-resource-widget', ResourceWidget);

    Statamic.$components.register('Pages/Runway/Index', Index);
    Statamic.$components.register('Pages/Runway/Create', Create);
    Statamic.$components.register('Pages/Runway/Edit', Edit);
});
