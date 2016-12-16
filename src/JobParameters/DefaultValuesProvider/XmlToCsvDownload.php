<?php

namespace Pim\Bundle\IcecatConnectorBundle\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DefaultParameters for simple XML import
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class XmlToCsvDownload implements DefaultValuesProviderInterface
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
            'filename'           => null,
            'download_directory' => '/tmp',
            'filePath'           => null,
            'uploadAllowed'      => false,
            'withHeader'         => true,
            'delimiter'          => ';',
            'enclosure'          => '"',
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
