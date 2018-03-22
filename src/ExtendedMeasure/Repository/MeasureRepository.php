<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Repository;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;

/**
 * Resolve a measure to a a PIM unit by its symbol.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasureRepository implements MeasureRepositoryInterface
{
    /**
     * Dictionnary indexed by symbols or units
     *
     * @var array
     */
    protected $dictionnary = [];

    /**
     * @param array $pimConfig
     */
    public function __construct(array $pimConfig)
    {
        $this->buildDictionnaries($pimConfig['measures_config']);
    }

    /**
     * {@inheritdoc}
     */
    public function find($unit, $family = null)
    {
        return $this->findInDictionnary($unit, $family);
    }

    /**
     * Find a symbol in internal dictionnary. We can add a filter on family
     *
     * @param string      $search
     * @param string|null $family
     *
     * @return mixed
     */
    protected function findInDictionnary($search, $family)
    {
        if (!isset($this->dictionnary[$search])) {
            throw new UnknownUnitException($search);
        }

        if (count($this->dictionnary[$search]) === 1) {
            return $this->dictionnary[$search][0];
        }

        // Resolve problem finding a unit
        $message = sprintf('Unable to resolve the unit "%s" in', $search);
        if (null === $family) {
            foreach ($this->dictionnary[$search] as $unresolvables) {
                $message .= sprintf(' [family: %s, unit: %s]', $unresolvables['family'], $unresolvables['unit']);
            }
            throw new UnresolvableUnitException($message);
        }

        $foundConfig = null;
        $foundCount = 0;
        foreach ($this->dictionnary[$search] as $unitConfig) {
            $message .= sprintf(' [family: %s, unit: %s]', $unitConfig['family'], $unitConfig['unit']);
            if ($unitConfig['family'] === $family) {
                $foundConfig = $unitConfig;
                ++$foundCount;
            }
        }

        if ($foundCount > 1) {
            throw new UnresolvableUnitException($message);
        }

        return $foundConfig;
    }

    /**
     * Build units and symbls dictionnaries to optimize search
     *
     * @param array $measuresConfig
     */
    protected function buildDictionnaries($measuresConfig)
    {
        foreach ($measuresConfig as $pimFamily => $units) {
            foreach ($units['units'] as $pimUnit => $unitConfig) {
                $unitConfig['family'] = $pimFamily;
                $unitConfig['unit'] = $pimUnit;
                $this->dictionnary[$pimUnit][] = $unitConfig;
                $this->buildUnit($unitConfig);
            }
        }
    }

    /**
     * Builds one unit definition with its keys:
     *      CUBIC_MILLIMETER:
     *          unece_code: 'MMQ'
     *          convert: [{'mul': 0.000000001}]
     *          symbol: 'mm³'
     *          name: 'cubic millimeter'
     *          alternative_symbols: ['foo', 'bar']
     *
     * @param array $unitConfig
     */
    protected function buildUnit(array $unitConfig)
    {
        $this->dictionnary[$unitConfig['symbol']][] = $unitConfig;

        if (isset($unitConfig['unece_code'])) {
            $this->dictionnary[$unitConfig['unece_code']][] = $unitConfig;
        }

        if (isset($unitConfig['alternative_symbols'])) {
            foreach ($unitConfig['alternative_symbols'] as $alternativeSymbol) {
                // process UTF8 entities. Using json_decode is a bit hacky, but it is the simplest way
                if (strpos($alternativeSymbol, '\u') === 0) {
                    $alternativeSymbol = json_decode('"' . $alternativeSymbol . '"');
                }
                $this->dictionnary[$alternativeSymbol][] = $unitConfig;
            }
        }
    }
}
