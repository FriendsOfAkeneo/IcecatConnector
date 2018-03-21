<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\IcecatConnectorBundle\Exception\MapperException;

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
    protected $mapping = null;

    /** @var ConfigManager */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getMapped($sourceItem)
    {
        if (null === $this->mapping) {
            $this->mapping = $this->loadMapping();
        }
        $targetItem = null;
        if (isset($this->mapping[$sourceItem])) {
            $targetItem = $this->mapping[$sourceItem];
        }

        return $targetItem;
    }

    protected function loadMapping()
    {
        $mappingFilePath = '/tmp/mapping.csv';

        if (!file_exists($mappingFilePath)) {
            throw new MapperException(
                sprintf('Mapping file "%s" does not exists', $mappingFilePath),
                MapperException::FILE_NOT_FOUND
            );
        }
        $fileHandle = fopen($mappingFilePath, 'r');

        // skip headers
        fgetcsv($fileHandle, 0, ';');

        $mapping = [];
        while ($row = fgetcsv($fileHandle, 0, ';')) {
            list($icecatId, $pimCode) = $row;
            $mapping[$icecatId] = $pimCode;
        }

        return $mapping;
    }
}
