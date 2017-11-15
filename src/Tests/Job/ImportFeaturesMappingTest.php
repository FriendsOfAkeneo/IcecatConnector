<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Job;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\IcecatConnectorBundle\Tests\AbstractTestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportFeaturesMappingTest extends AbstractTestCase
{
    /** @var string */
    private $jobCode = 'icecat_import_features_mapping';

    public function additionnalSetup()
    {
        $this->createImportProfile('Icecat', $this->jobCode);
        if (file_exists('/tmp/mapping.csv')) {
            unlink('/tmp/mapping.csv');
        }
    }

    public function testImportGeneratesWarningOnNotFoundAttribute()
    {
        $input = [
            'code' => $this->jobCode,
            '--config' => '{"filePath": "' . realpath(__DIR__ . '/../../Resources/jenkins/featuresList.csv') . '"}',
        ];

        $res = $this->runBatchCommand($input);
        $this->assertEquals(BatchCommand::EXIT_WARNING_CODE, $res);
        $jobRepo = $this->get('akeneo_batch.job.job_instance_repository');
        /** @var JobInstance $job */
        $job = $jobRepo->findOneByIdentifier($this->jobCode);

        /** @var JobExecution $execution */
        $execution = $job->getJobExecutions()->last();
        $warnings = [];
        foreach ($execution->getStepExecutions() as $stepExecution) {
            $warnings = array_merge($warnings, $stepExecution->getWarnings()->toArray());
        }

        $this->assertCount(5, $warnings);
        $this->assertEquals(sprintf('The "%s" attribute code does not exist.', 'icecat_numeric_keypad'), $warnings[0]->getReason());

        $this->assertTrue(file_exists('/tmp/mapping.csv'));
        $this->assertEquals(0, filesize('/tmp/mapping.csv'));
    }

    public function testValidImportGeneratesMappingFile()
    {
        $this->loadData();

        $input = [
            'code' => $this->jobCode,
            '--config' => '{"filePath": "' . realpath(__DIR__ . '/../../Resources/jenkins/featuresList.csv') . '"}',
        ];
        $res = $this->runBatchCommand($input);
        $this->assertEquals(BatchCommand::EXIT_WARNING_CODE, $res);

        $this->assertTrue(file_exists('/tmp/mapping.csv'));
        $expectedData = [
            ['feature_id', 'pim_attribute_code'],
            ['1006', 'icecat_numeric_keypad'],
            ['11379', 'icecat_processor_frequency'],
            ['3233', 'icecat_operating_system'],
            ['11381', 'icecat_installed_ram'],
            ['21719', 'icecat_processor_series'],
        ];

        $f = fopen('/tmp/mapping.csv', 'r');
        while ($data = fgetcsv($f, 0, ';')) {
            $this->assertTrue(in_array($data, $expectedData));
        }
    }
}
