<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Job;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Pim\Bundle\IcecatConnectorBundle\Tests\AbstractTestCase;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnrichProductsTest extends AbstractTestCase
{
    private $jobCode = 'icecat_enrich_products';

    public function setUp()
    {
        parent::setUp();
        $this->createImportProfile('Icecat', $this->jobCode);
        $this->loadData();
    }

    public function testEanProductIsEnriched()
    {
        $dataProduct = [
            'family' => 'icecat_laptop',
            'values' => [
                'sku' => [[
                    'data' => 'myicecatlaptop',
                    'locale' => null,
                    'scope' => null,
                ]],
                'icecat_ean' => [[
                    'data' => '0190780203514',
                    'locale' => null,
                    'scope' => null,
                ]],
            ],
        ];
        $product = $this->get('pim_catalog.builder.product')->createProduct();
        $this->get('pim_catalog.updater.product')->update($product, $dataProduct);
        $this->get('pim_catalog.saver.product')->save($product);

        copy(__DIR__ . '/../../Resources/jenkins/mapping.csv', '/tmp/mapping.csv');


        $input = [
            'code' => $this->jobCode,
        ];
        $res = $this->runBatchCommand($input);

        $this->assertEquals(BatchCommand::EXIT_SUCCESS_CODE, $res);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('myicecatlaptop');
        $this->assertInstanceOf(ProductInterface::class, $product);

        $mappedAttributes = [
            'icecat_numeric_keypad',
            'icecat_installed_ram',
            'icecat_processor_series',
            'icecat_processor_frequency',
            'icecat_operating_system',
        ];

        foreach ($mappedAttributes as $mappedAttribute) {
            $this->assertNotNull($product->getValue($mappedAttribute));
        }
    }
}
