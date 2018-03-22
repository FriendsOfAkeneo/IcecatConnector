<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Exception;

/**
 * Exception thrown when tryin to identify a unit not in PIM config.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class UnknownUnitException extends \RuntimeException
{
    public function __construct($unit, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unkown unit "%s"', $unit);
        parent::__construct($message, $code, $previous);
    }
}
