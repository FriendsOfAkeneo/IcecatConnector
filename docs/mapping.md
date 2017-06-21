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
Alternatively, we can also simply delete the unwanted features lines.

#### Helpers

The only columns used in the mapping are the three first columns.
The other columns are here just to help you in the mapping.
It is easier to identify the Icecat features by their name and description than just relying on the feature ID.

## Mapping import

Once the mapping is finished, we can upload the file using the corresponding job `icecat_import_features_mapping`.

This import will perform some simple validations and warn you in case of inconsistent mapping,
like a text feature mapped to a number attribute.
All these validations are just warnings and you will be responsible to keep the existing mapping or to fix it.
For instance, it is possible that you need a number feature into a text attribute.
In this case, the validation will emit a warning but you will keep the mapping.
