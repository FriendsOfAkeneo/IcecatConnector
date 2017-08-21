<?php

namespace Pim\Bundle\IcecatConnectorBundle\Form\Type\MassAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form type is used to provide possible users choices when performing the "icecat enrichment" mass action.
 * Since there are no information to expect from users, the class does not add any field to the form.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IcecatEnrichmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\\Bundle\\IcecatConnectorBundle\\MassEditAction\\Operation\\IcecatEnrichment'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_icecat_mass_action_operation_icecat_enrichment';
    }
}
