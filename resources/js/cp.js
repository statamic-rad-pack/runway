import PublishForm from './components/Publish/PublishForm.vue'
import ResourceView from './components/ResourceView.vue'
import HasManyFieldtypeIndex from '../../vendor/statamic/cms/resources/js/components/fieldtypes/relationship/RelationshipIndexFieldtype.vue'

Statamic.$components.register('runway-publish-form', PublishForm)
Statamic.$components.register('resource-view', ResourceView)
Statamic.$components.register('has_many-fieldtype-index', HasManyFieldtypeIndex)
