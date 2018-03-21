<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\IcecatConnectorBundle\Measure\MeasureParser;
use Prewk\XmlStringStreamer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * A test utility to extract product ID and one EAN from the public product list:
 * http://data.icecat.biz/export/freexml.int/EN/
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFixturesParserCommand extends ContainerAwareCommand
{
    const FIXTURES_URL = 'http://data.icecat.biz/export/freexml.int/EN/';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:parser:product-fixtures');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->write($output, 'Start fixtures download');
        $inputFilepath = '/tmp/products.xml';
        $outputFilepath = '/tmp/products_fixtures.csv';
        $httpClient = $this->getContainer()->get('pim_icecat_connector.http.client');
        $guzzle = $httpClient->getGuzzle();
        $guzzle->request('GET', self::FIXTURES_URL, [
            'auth' => $httpClient->getCredentials(),
            'sink' => $inputFilepath,
        ]);

        $this->write($output, sprintf('Download success, filesize = %d', filesize($inputFilepath)));

        $this->write($output, sprintf('Start parsing file <info>%s</info>', $inputFilepath));

        $streamer = XmlStringStreamer::createStringWalkerParser($inputFilepath, [
            'captureDepth' => 3,
        ]);

        $data = [
            ['sku', 'icecat_id', 'family']
        ];
        while ($node = $streamer->getNode()) {
            $element = simplexml_load_string($node);
            $attributes = $element->attributes();
            $icecatId = (int) $attributes['Product_ID'];
            $icecatSku = (string) $attributes['Prod_ID'];
            $icecatSku = preg_replace('/\W/', '_', $icecatSku);
            $data[] = [
                $icecatSku,
                $icecatId,
                'icecat'
            ];
            if (count($data) > 2000) {
                break;
            }
        }

        $this->write($output, sprintf('%d fixtures generated', count($data)));

        $fp = fopen($outputFilepath, 'w');

        foreach ($data as $line) {
            fputcsv($fp, $line, ';');
        }

        fclose($fp);

        $this->write($output, sprintf('Write fixtures success, filesize = %d', filesize($outputFilepath)));

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseXml(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('filepath');
        $this->write($output, sprintf('Start parsing file <info>%s</info>', $filepath));

        $streamer = XmlStringStreamer::createStringWalkerParser($filepath, [
            'captureDepth' => 1,
        ]);

        $parser = new MeasureParser();
        $measureRepository = $this->getContainer()->get('pim_extended_measures.repository');

        $mesureCount = 0;
        $excludedCount = 0;
        $unknown = [];

        while ($node = $streamer->getNode()) {
            $simpleXmlNode = simplexml_load_string($node);
            $measure = $parser->parseNode($simpleXmlNode);
            try {
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
