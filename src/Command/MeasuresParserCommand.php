<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\IcecatConnectorBundle\Measure\MeasureParser;
use Prewk\XmlStringStreamer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasuresParserCommand extends ContainerAwareCommand
{
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
        $filepath = $input->getArgument('filepath');
        $this->write($output, sprintf('Start parsing file <info>%s</info>', $filepath));

        $streamer = XmlStringStreamer::createStringWalkerParser($filepath, [
            'captureDepth' => 4,
        ]);

        $parser = new MeasureParser();
        $measureRepository = $this->getContainer()->get('pim_extended_measures.repository');

        $mesureCount = 0;
        $unknown = [];

        while ($node = $streamer->getNode()) {
            try {
                $simpleXmlNode = simplexml_load_string($node);
                $measure = $parser->parseNode($simpleXmlNode);
                $mesureCount++;
                $measureRepository->findByUnit($measure->getSign());
            } catch (UnknownUnitException $e) {
                $code = strtoupper(preg_replace('/[- ]/', '_', $measure->getName()));
                $unknown[$code] = [
                    'name'        => $measure->getName(),
                    'conv'        => [['mul' => 1]],
                    'symbol'      => $measure->getSign(),
                    'description' => $measure->getDescription(),
                ];
                $this->write($output, $e->getMessage());
            } catch (UnresolvableUnitException $e) {
                $this->write($output, '<error>' . $e->getMessage() . '</error>');
            }
        }

        $errorRatio = count($unknown) / $mesureCount;
        $this->write($output, 'Read = <info>' . $mesureCount . '</info>');
        $this->write($output, 'Errors = <info>' . count($unknown) . '</info>');
        $this->write($output, 'Error ratio = <info>' . $errorRatio * 100 . '%</info>');

        $unknown = [
            'measures_config' => [
                'UnknownMeasures' => $unknown,
            ],
        ];
        $yaml = Yaml::dump(['measures_config' => $unknown], 5);
        file_put_contents('/tmp/icecat-measures.yml', $yaml);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    private function write(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }
}
