<?php

namespace Pim\Bundle\IcecatConnectorBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\IcecatConnectorBundle\Entity\Feature;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Updates a feature
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function update($feature, array $data, array $options = [])
    {
        if (!$feature instanceof Feature) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    Feature::class,
                    ClassUtils::getClass($feature)
                )
            );
        }

        $feature->setId($data['id']);
        $feature->setType($data['type']);
        if (isset($data['name'])) {
            $feature->setName($data['name']);
        }
        if (isset($data['description'])) {
            $feature->setDescription($data['description']);
        }
        if (isset($data['sign'])) {
            $feature->setDescription($data['sign']);
        }

        return $this;
    }
}
