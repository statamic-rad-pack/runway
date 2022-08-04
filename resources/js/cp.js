import RelationshipFieldtype from "./components/Fieldtypes/RelationshipFieldtype";
import HasManyRelatedItem from "./components/Fieldtypes/HasManyRelatedItem";
import PublishForm from "./components/Publish/PublishForm.vue";
import ListingView from "./components/Listing/ListingView.vue";

Statamic.$components.register(
  "runway-relationship-fieldtype",
  RelationshipFieldtype
);
Statamic.$components.register("hasmany-related-item", HasManyRelatedItem);
Statamic.$components.register("runway-publish-form", PublishForm);
Statamic.$components.register("runway-listing-view", ListingView);

/**
 * Pull in index fieldtypes (for reasons...)
 */

import HasManyFieldtypeIndex from "../../vendor/statamic/cms/resources/js/components/fieldtypes/relationship/RelationshipIndexFieldtype.vue";

Statamic.$components.register(
  "has_many-fieldtype-index",
  HasManyFieldtypeIndex
);
