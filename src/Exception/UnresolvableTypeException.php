<?php

namespace Pim\Bundle\IcecatConnectorBundle\Exception;

use Pim\Bundle\IcecatConnectorBundle\Model\Feature;

class UnresolvableTypeException extends \RuntimeException
{
    /** @var Feature */
    protected $feature;

    /** @var string */
    protected $message = 'Unable to resolve Icecat type "%s"';

    /**
     * @param Feature    $feature
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(Feature $feature, $code = 0, \Exception $previous = null)
    {
        $this->feature = $feature;
        $message = sprintf($this->message, $feature->getType());
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
