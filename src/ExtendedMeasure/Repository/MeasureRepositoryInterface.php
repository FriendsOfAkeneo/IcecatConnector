<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Repository;

/**
 * Measures repository
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface MeasureRepositoryInterface
{
    /**
     * Retrieves a PIM unit from a symbol or unit
     *
     * @param string      $symbol
     * @param string|null $family to restrict the search in one family
     *
     * @return array
     */
    public function find($symbol, $family = null);
}
