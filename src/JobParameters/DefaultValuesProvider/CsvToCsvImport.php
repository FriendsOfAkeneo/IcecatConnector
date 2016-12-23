<?php

namespace Pim\Bundle\IcecatConnectorBundle\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DefaultParameters for simple CSV to CSV import
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvToCsvImport implements DefaultValuesProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /** @var array */
    protected $config;

    /**
     * @param array $supportedJobNames
     * @param array $config
     */
    public function __construct(array $supportedJobNames, array $config)
    {
        $this->supportedJobNames = $supportedJobNames;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'filePath'        => '/tmp/featuresList.csv',
            'output_filepath' => null,
            'uploadAllowed'   => true,
            'withHeader'      => true,
            'delimiter'       => ';',
            'enclosure'       => '"',
        ]);

        return $resolver->resolve($this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
