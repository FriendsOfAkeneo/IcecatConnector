<?php

namespace Pim\Bundle\IcecatConnectorBundle\Reader\Xml;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\IcecatConnectorBundle\Parser\FeatureToAttributeParser;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Prewk\XmlStringStreamer;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeaturesReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var XmlStringStreamer */
    protected $xmlStreamer;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    public function __construct(ArrayConverterInterface $arrayConverter)
    {
        $this->arrayConverter = $arrayConverter;
    }

    /**
     * {@inheritdoc}
     *
     * @return array Attribute array, standard PIM format
     * @see https://github.com/akeneo/pim-community-dev/blob/master/STANDARD_FORMAT.md
     */
    public function read()
    {
        if (null === $this->xmlStreamer) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->get('filePath');
            $this->xmlStreamer = XmlStringStreamer::createStringWalkerParser($filePath);
        }
        $node = $this->xmlStreamer->getNode();

        if (null === $node) {
            return null;
        }

        $simpleXmlNode = simplexml_load_string($node);
        $parser = new FeatureToAttributeParser();
        $feature = $parser->parseNode($simpleXmlNode);

        return $parser->normalize($feature);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
