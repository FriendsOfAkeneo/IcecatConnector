<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IgnoreFlagConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($item, Constraint $constraint)
    {
        $ignoreFlag = $item['ignore_flag'];
        $alowedValues = ['0', '1'];
        if (!in_array($ignoreFlag, $alowedValues)) {
            $message = sprintf($constraint->message, $ignoreFlag);
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
