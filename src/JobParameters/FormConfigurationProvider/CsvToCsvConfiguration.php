<?php

namespace Pim\Bundle\IcecatConnectorBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

/**
 * Form options for a CSV to CSV import
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvToCsvConfiguration implements FormConfigurationProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /**
     * @param array $supportedJobNames
     */
    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration()
    {
        return [
            'output_filepath' => [
                'options' => [
                    'label' => 'pim_connector.export.csv.filePath.label',
                    'help'  => 'pim_connector.export.csv.filePath.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.export.csv.delimiter.label',
                    'help'  => 'pim_connector.export.csv.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.export.csv.enclosure.label',
                    'help'  => 'pim_connector.export.csv.enclosure.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.csv.uploadAllowed.label',
                    'help'  => 'pim_connector.import.csv.uploadAllowed.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
