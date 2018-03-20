<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\BatchBundle\Command\CreateJobCommand;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractTestCase extends KernelTestCase
{
    /** @var array */
    private $credentials = [
        'username' => null,
        'password' => null,
    ];

    /** @var KernelInterface */
    protected $testKernel;

    /** @var CatalogInterface */
    protected $catalog;

    public function setUp()
    {
        static::bootKernel(['debug' => false]);
        $config = $this->get('oro_config.global');
        $this->credentials['username'] = $config->get('pim_icecat_connector.credentials_username');
        $this->credentials['password'] = $config->get('pim_icecat_connector.credentials_password');

        $container = static::$kernel->getContainer();
        $authenticator = new SystemUserAuthenticator($container);
        $authenticator->createSystemUser();

        $kernelClass = class_exists(
            'PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle'
        ) ? AppKernelTestEe::class : AppKernelTest::class;

        $this->testKernel = new $kernelClass('test', false);
        $this->testKernel->boot();

        $this->catalog = $this->testKernel->getContainer()->get('akeneo_integration_tests.configuration.catalog');
        $this->testKernel->getContainer()->set(
            'akeneo_integration_tests.catalog.configuration',
            $this->catalog->useMinimalCatalog()
        );

        $fixturesLoader = $this->testKernel->getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load();

        $this->additionnalSetup();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $em->clear();
    }

    protected function additionnalSetup()
    {
        // implement in sub classes
    }

    /**
     * @param $serviceName
     *
     * @return object
     */
    protected function get($serviceName)
    {
        return $this->testKernel->getContainer()->get($serviceName);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->testKernel->getContainer()->getParameter($name);
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param array $input
     *
     * @return int
     */
    protected function runBatchCommand(array $input = [])
    {
        $application = new Application($this->testKernel);
        $batchCommand = new BatchCommand();
        $batchCommand->setContainer($this->testKernel->getContainer());
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
        $application = new Application($this->testKernel);
        $batchCommand = new CreateJobCommand();
        $batchCommand->setContainer($this->testKernel->getContainer());
        $application->add($batchCommand);
        $cmd = $application->find('akeneo:batch:create-job');
        $input = new ArrayInput([
            'connector' => $connector,
            'job'       => $job,
            'type'      => 'import',
            'code'      => $job,
        ]);
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
        $this->get('pim_catalog.updater.family')->update($family, [
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
        ]);
        $this->get('pim_catalog.saver.family')->save($family);
        sleep(5);
    }
}
