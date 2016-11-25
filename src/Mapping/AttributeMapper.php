<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping;

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

    public function __construct()
    {
        $this->mapping = [
            'product_description ' => 'product_description',
            'long_description'     => 'description',
            'short_description'    => 'short_description',
//            537=>'537',
        ];
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
}
