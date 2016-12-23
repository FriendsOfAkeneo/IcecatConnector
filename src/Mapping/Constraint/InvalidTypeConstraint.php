<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidTypeConstraint extends Constraint
{
    /** @var AttributeInterface */
    protected $attribute;

    /** @var string */
    public $message = 'Feature %d of Icecat type "%s" mapped to "%s" [type "%s"] should be mapped to a "%s" attribute type.';

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['attribute'];
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
