<?php

namespace Pim\Bundle\IcecatConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\IcecatConnectorBundle\Xml\IcecatDownloader;
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

    /** @var IcecatDownloader */
    private $downloader;

    public function __construct(IcecatDownloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->xmlStreamer) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filename = $jobParameters->get('filename');
            $downloadDirectory = $jobParameters->get('download_directory');
            $this->stepExecution->addSummaryInfo('download.start', $filename);
            $downloadedFile = $this->downloader->download($filename, $downloadDirectory, true);
            $this->stepExecution->addSummaryInfo('download.success', $downloadedFile);
            $this->xmlStreamer = XmlStringStreamer::createStringWalkerParser($downloadedFile, [
                'captureDepth' => 4,
            ]);
        }
        $node = $this->xmlStreamer->getNode();

        if (null === $node) {
            return null;
        }

        $xmlElement = simplexml_load_string($node);

        if (false === $xmlElement) {
            return null;
        }

        $this->stepExecution->incrementSummaryInfo('read_lines');

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
