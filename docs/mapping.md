# Icecat Connector extension - Mapping

## Icecat features list

The extension is shipped with a dedicated import profile to download all the Icecat features in a CSV file
ready to be edited to set the mapping between Icecat features and Akeneo attributes.

You can find an example of this file [here](files/featuresList.csv).

![featuresList.csv](img/featuresList-01.png?raw=true)

### featuresList.csv headers

* feature_id: the Icecat feature ID. Should not be modified.
* pim_attribute_code: the attribute code to map.
* ignore_flag: 0 or 1. A flag to specifically ignore this feature. _(see below)_
* feature_name: The feature name in Icecat system. Provided only to help in the mapping.
* feature_description: The feature description in Icecat system. Provided only to help in the mapping.
* feature_unit: The feature measure unit in Icecat system. Provided only to help in the mapping.

#### The ignore_flag

You probably will not need to map all Icecat features. Setting this flag to 1 will tell the mapper to ignore this feature.
This flag system is here to ensure you didn't forget some features.
Features not mapped and not ignored will raise an error in the import mapping job.

#### Helpers

The only columns used in the mapping are the three first columns.
The other columns are here just to help you in the mapping.
It is easier to identify the Icecat features by their name and description than just relying on the feature ID.

## Mapping import

_TODO_
