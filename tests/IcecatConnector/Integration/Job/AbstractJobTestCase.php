<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Job;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\BatchBundle\Command\CreateJobCommand;
use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\IcecatConnectorBundle\Tests\AbstractTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractJobTestCase extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadData();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): ?Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array $input
     *
     * @return int
     */
    protected function runBatchCommand(array $input = [])
    {
        $application = new Application(static::$kernel);
        $batchCommand = new BatchCommand();
        $batchCommand->setContainer(static::$kernel->getContainer());
        $application->add($batchCommand);

        if (!array_key_exists('--no-log', $input)) {
            $input['--no-log'] = true;
        }

        $batch = $application->find('akeneo:batch:job');

        return $batch->run(new ArrayInput($input), new NullOutput());
    }

    /**
     * Creates an import profile
     *
     * @param string $connector
     * @param string $job
     */
    protected function createImportProfile($connector, $job)
    {
        $application = new Application(static::$kernel);
        $batchCommand = new CreateJobCommand();
        $batchCommand->setContainer(static::$kernel->getContainer());
        $application->add($batchCommand);
        $cmd = $application->find('akeneo:batch:create-job');
        $input = new ArrayInput(
            [
                'connector' => $connector,
                'job'       => $job,
                'type'      => 'import',
                'code'      => $job,
            ]
        );
        $cmd->run($input, new NullOutput());
    }

    protected function loadData()
    {
        $attributes = [
            [
                'code'   => 'icecat_ean',
                'type'   => AttributeTypes::TEXT,
                'unique' => true,
                'group'  => 'other',
            ],
            [
                'code'  => 'icecat_numeric_keypad',
                'type'  => AttributeTypes::BOOLEAN,
                'group' => 'other',
            ],
            [
                'code'                => 'icecat_processor_frequency',
                'type'                => AttributeTypes::METRIC,
                'metric_family'       => 'Frequency',
                'negative_allowed'    => false,
                'decimals_allowed'    => true,
                'default_metric_unit' => 'MEGAHERTZ',
                'group'               => 'other',
            ],
            [
                'code'                => 'icecat_installed_ram',
                'type'                => AttributeTypes::METRIC,
                'metric_family'       => 'Binary',
                'negative_allowed'    => false,
                'decimals_allowed'    => true,
                'default_metric_unit' => 'GIGABYTE',
                'group'               => 'other',
            ],
            [
                'code'  => 'icecat_processor_series',
                'type'  => AttributeTypes::TEXT,
                'group' => 'other',
            ],
            [
                'code'  => 'icecat_operating_system',
                'type'  => AttributeTypes::TEXT,
                'group' => 'other',
            ],
        ];

        $attributeFactory = $this->get('pim_catalog.factory.attribute');
        $attributeUpdater = $this->get('pim_catalog.updater.attribute');
        $attributeSaver = $this->get('pim_catalog.saver.attribute');

        foreach ($attributes as $data) {
            $attribute = $attributeFactory->create();
            $attributeUpdater->update($attribute, $data);
            $attributeSaver->save($attribute);
        }

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code'               => 'icecat_laptop',
                'attribute_as_label' => 'sku',
                'attributes'         => [
                    'icecat_ean',
                    'icecat_numeric_keypad',
                    'icecat_installed_ram',
                    'icecat_processor_series',
                    'icecat_processor_frequency',
                    'icecat_operating_system',
                ],
            ]
        );
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
