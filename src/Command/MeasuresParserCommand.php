<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\IcecatConnectorBundle\Parser\MeasuresParser;
use Prewk\XmlStringStreamer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasuresParserCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:parser:measures')
            ->addArgument(
                'filepath',
                InputArgument::REQUIRED,
                'The measures list filepath.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $filepath = $input->getArgument('filepath');
        $this->write(sprintf('Start parsing file <info>%s</info>', $filepath));

        $streamer = XmlStringStreamer::createStringWalkerParser($filepath, [
            'captureDepth' => 4,
        ]);

        $parser = new MeasuresParser();
        $resolver = $this->getContainer()->get('pim_extended_measures.resolver');

        $outputFile = '/tmp/measures.csv';
        touch($outputFile);

        while ($node = $streamer->getNode()) {
            try {
                $simpleXmlNode = simplexml_load_string($node);
                $measure = $parser->parseNode($simpleXmlNode);
                $pimMeasure = $resolver->resolvePimMeasure($measure->getSign());
            } catch (UnknownUnitException $e) {
                $this->write($e->getMessage());
            } catch (UnresolvableUnitException $e) {
                $this->write('<error>' . $e->getMessage() . '</error>');
            }
        }
    }

    /**
     * @param string $message
     */
    private function write($message)
    {
        $this->output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }
}
