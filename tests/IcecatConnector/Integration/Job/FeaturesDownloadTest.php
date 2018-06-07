<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Jobs;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Pim\Bundle\IcecatConnectorBundle\Tests\Job\AbstractJobTestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeaturesDownloadTest extends AbstractJobTestCase
{
    /** @var string */
    private $jobCode = 'icecat_download_features';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createImportProfile('Icecat', $this->jobCode);
    }

    public function testJobExecution()
    {
        $job = $this->get('pim_icecat_connector.job.job_parameters.default_values_provider.download_xml_to_csv');
        $config = $job->getDefaultValues();
        if (file_exists($config['filePath'])) {
            unlink($config['filePath']);
        }
        $icecatDownloadedFile = $config['download_directory'] . DIRECTORY_SEPARATOR . $config['filename'];
        if (file_exists($icecatDownloadedFile)) {
            unlink($icecatDownloadedFile);
        }

        $input = [
            'code' => 'icecat_download_features',
        ];

        $res = $this->runBatchCommand($input);
        $this->assertEquals(BatchCommand::EXIT_SUCCESS_CODE, $res);
        $this->assertTrue(file_exists($icecatDownloadedFile));
        $this->assertTrue(file_exists($config['filePath']));

        $f = fopen($config['filePath'], 'r');
        $line = fgetcsv($f, 0, ';');
        fclose($f);

        $this->assertEquals(['feature_id', 'pim_attribute_code', 'ignore_flag', 'feature_type', 'feature_name', 'feature_description', 'feature_unit'], $line);
    }
}
