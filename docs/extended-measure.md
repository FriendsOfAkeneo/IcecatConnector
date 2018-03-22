# ExtendedMeasure bundle

Manage measure units in families and conversions from a unit to another

Allows to :
- Convert a value from a unit to another
- Add more unit to a family (group of measure units)
- Create new families

## Measure configuration structure

A measure configuration YAML file is like this example:

```
measures_config:
    Acceleration:
        standard: METER_PER_SQUARE_SECOND
        units:
            METER_PER_SQUARE_SECOND:
                convert: [{'mul': 1}]
                symbol: 'm/s²'
                name: 'meter per square second'
                unece_code: 'MTS'
                alternative_units: ['mdivs²']
```

- `measures_config`: the symfony extension configuration key.
- `Acceleration`: the measure family. In our case, we define units for physical acceleration.
- `standard`: this key defines which unit will be used as the base unit for this family.
- `units`: allunits of the family.
- `METER_PER_SQUARE_SECOND`: a readable name for the unit.
- `convert`: array of operations to convert this unit to the standard unit.
- `symbol`: the usual symbol of the measure.
- `name`: readable name useable as a label.
- `unece_code`: code of the measure in the UNECE convention.
- `alternative_symbols`: other symbols or names we could find for this same measure.
 The symbol can vary from one standard to another.

## What's new

- `alternative_symbols`: one measure can be identified with multiple symbols, 
 to reflect the differences existing between measures systems. 
 It can also be used to add diferent encoding of the same caracter like `µ` 
 which can be encoded with the 'micro' UTF8 code or the greak 'mu' UTF8 code.
 
- `unece_code`: alphanumeric identifier of the UNECE convention. 
 Used by CNET for example (see http://www.unece.org/cefact/codesfortrade/codes_index.html)

- A lot of new measures. So we had to split the configuration in multiple yaml files 
 inside the `Resources/config/measures` directory.

## Console commands

Because the number of measures is greatly increased, we provide some console command to help 
keeping this configuration clean and to find informations about measures.

- `pim:measures:check`:
parse all configuration files and check for the unicity of a unit inside the same family
 
- `pim:measures:find`:
find a measure by one of its unit and return some information.
