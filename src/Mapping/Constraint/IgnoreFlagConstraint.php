<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IgnoreFlagConstraint extends Constraint
{
    public $message = 'The "ignore_flag" must be 0 or 1, %s provided';
}
