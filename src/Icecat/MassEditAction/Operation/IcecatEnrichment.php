<?php

namespace Pim\Bundle\IcecatConnectorBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation;

/**
 * This class describes what actions and options are available for the mass action.
 * In this case, we don't need to specify any of them as:
 * - locale/fallback locale are set in the Configuration screen
 * - channel choice is also set in the Configuration screen.
 *
 * In the future, it may be an idea to update this part so one can choose a different locale on-the-fly.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IcecatEnrichment extends AbstractMassEditOperation
{
    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'icecat-enrichment';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_icecat_mass_action_operation_icecat_enrichment';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }
}
