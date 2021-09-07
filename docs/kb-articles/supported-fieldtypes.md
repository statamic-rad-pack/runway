---
title: 'Supported Fieldtypes'
---

Runway supports pretty much ALL fieldtypes available in Statamic, including Bard.

As long as you have the correct fieldtype and the correct column type, everything should work fine!

Here’s a real simple table, matching the fieldtypes with the column types.


**Fieldtype**|**Column Type**|**Notes**
-----|-----|-----
[Array](https://statamic.dev/fieldtypes/array)|`json`|
[Assets](https://statamic.dev/fieldtypes/assets)|`string`/`json`|
[Bard](https://statamic.dev/fieldtypes/bard)|`string`/`json`|If 'Display HTML' is `true`, then Bard will save as a `string`.
[Button Group](https://statamic.dev/fieldtypes/button_group)|`string`|
[Checkboxes](https://statamic.dev/fieldtypes/checkboxes)|`json`|
[Code](https://statamic.dev/fieldtypes/code)|`string`|
[Collections](https://statamic.dev/fieldtypes/collections)|`string`/`json`|If 'Allow multiple' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Color](https://statamic.dev/fieldtypes/color)|`string`|
[Date](https://statamic.dev/fieldtypes/date)|`string`/`range`|Format is specified field configuration options. Ranges are should be stored as json.
[Entries](https://statamic.dev/fieldtypes/entries)|`string`/`json`|If 'Allow multiple' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Fieldset](https://statamic.dev/fieldtypes/fieldset)|Depends on the fields being imported.|The columns depend on the fields being imported by your fieldset. You may import a fieldset using `import: fieldset_handle`. (the automatic migration generator does not support fieldsets)
Float|`float`|
[Grid](https://statamic.dev/fieldtypes/grid)|`json`|
[Hidden](https://statamic.dev/fieldtypes/hidden)|`string`|
[HTML](https://statamic.dev/fieldtypes/html)|-|UI only
[Integer](https://statamic.dev/fieldtypes/integer)|`integer`|
[Link](https://statamic.dev/fieldtypes/link)|`json`|
[List](https://statamic.dev/fieldtypes/list)|`json`|
[Markdown](https://statamic.dev/fieldtypes/markdown)|`string`|
[Radio](https://statamic.dev/fieldtypes/radio)|`string`|
[Range](https://statamic.dev/fieldtypes/range)|`string`|
[Replicator](https://statamic.dev/fieldtypes/replicator)|`json`|
[Revealer](https://statamic.dev/fieldtypes/revealer)|-|UI only
[Section](https://statamic.dev/fieldtypes/section)|-|UI only
[Select](https://statamic.dev/fieldtypes/select)|`string`/`integer`/`json`|
[Structures](https://statamic.dev/fieldtypes/structures)|`json`|
[Table](https://statamic.dev/fieldtypes/table)|`json`|
[Tags](https://statamic.dev/fieldtypes/tags)|`json`|
[Template](https://statamic.dev/fieldtypes/template)|`string`|
[Terms](https://statamic.dev/fieldtypes/terms)|`string`/`json`|
[Text](https://statamic.dev/fieldtypes/text)|`string`|
[Textarea](https://statamic.dev/fieldtypes/textarea)|`string`|
[Time](https://statamic.dev/fieldtypes/time)|`string`|
[Toggle](https://statamic.dev/fieldtypes/toggle)|`boolean`|
[Users](https://statamic.dev/fieldtypes/users)|`string`/`integer`/`json`|
[Video](https://statamic.dev/fieldtypes/video)|`string`|
[YAML](https://statamic.dev/fieldtypes/yaml)|`string`|
[Belongs To](/fieldtypes#belongsto-fieldtype)|`bigInteger`|Usually `bigInteger` or `integer` but depends on personal preference.
