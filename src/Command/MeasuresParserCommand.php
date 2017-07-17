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
 * Parse all Icecat measures and check their existence in Akeneo
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $excludedCount = 0;
        $unknown = [];

        while ($node = $streamer->getNode()) {
            try {
                $simpleXmlNode = simplexml_load_string($node);
                $measure = $parser->parseNode($simpleXmlNode);
                $mesureCount++;
                $measureRepository->find($measure->getSign());
            } catch (UnknownUnitException $e) {
                if (in_array($measure->getSign(), $this->getIgnoredSigns())) {
                    $excludedCount++;
                    continue;
                }
                $code = strtoupper(preg_replace('/[- ]/', '_', $measure->getName()));
                $unknown[$code] = [
                    'name'        => $measure->getName(),
                    'convert'     => [['mul' => 1]],
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
        $this->write($output, 'Excluded = <info>' . $excludedCount . '</info>');
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

    protected function getIgnoredSigns()
    {
        return [
            '',
            '€',
            'cent(s)',
            'x',
            'lines',
            'pages',
            'entries',
            'slides',
            'levels of grey',
            'user(s)',
            'person(s)',
            'sheets',
            'buttons',
            'locations',
            'scans',
            'pass(es)',
            'scans',
            'discs',
            'copies',
            'clicks',
            'label(s)', 'coins', 'shots',
            'coins per minute', 'octave(s)', 'piece(s)', 'EER', 'staples', 'cycles per logical sector',
            'banknotes/min', 'pc(s)',

        ];
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
