<?php

namespace Pim\Bundle\IcecatConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Prewk\XmlStringStreamer;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var XmlStringStreamer */
    protected $xmlStreamer;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->xmlStreamer) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->get('filePath');
            $this->xmlStreamer = XmlStringStreamer::createStringWalkerParser($filePath);
        }
        $node = $this->xmlStreamer->getNode();
        $this->stepExecution->incrementSummaryInfo('read_lines');

        if (null === $node) {
            return null;
        }

        $xmlElement = simplexml_load_string($node);

        return $xmlElement;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
