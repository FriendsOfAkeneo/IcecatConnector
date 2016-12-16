<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMapper implements MapperInterface
{
    /**
     * @var array
     */
    protected $mapping = [];

    /** @var ConfigManager */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->loadMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function getMapped($sourceItem)
    {
        $targetItem = null;
        if (isset($this->mapping[$sourceItem])) {
            $targetItem = $this->mapping[$sourceItem];
        }

        return $targetItem;
    }

    protected function loadMapping()
    {

    }
}
