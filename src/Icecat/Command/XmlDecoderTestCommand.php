<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * A command to test the XmlProductDecoder with an inline XML file
 *
 * Usage:
 * bin/console pim-icecat:xml:decode EN vendor/akeneo/icecat-connector/tests/resources/808736152730_en.xml --env=prod
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlDecoderTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:xml:decode')
            ->addArgument(
                'icecatLocale',
                InputArgument::REQUIRED,
                'Icecat locale of the file.'
            )
            ->addArgument(
                'filepath',
                InputArgument::REQUIRED,
                'Absolute filepath to the XML file.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('filepath');
        $icecatLocale = $input->getArgument('icecatLocale');

        $this->write($output, 'Load XML');
        $xmlString = file_get_contents($filepath);

        $localeResolver = $this->getContainer()->get('pim_icecat_connector.resolver.locale');
        $decoder = $this->getContainer()->get('pim_icecat_connector.decoder.xml.product');

        $configManager = $this->getContainer()->get('oro_config.global');
        $fallbackLocale = $configManager->get('pim_icecat_connector.fallback_locale');

        $context = [
            'locale'          => $localeResolver->getPimLocaleCode($icecatLocale),
            'fallback_locale' => $localeResolver->getPimLocaleCode($fallbackLocale),
        ];
        $standardProduct = $decoder->decode($xmlString, 'xml', $context);

        VarDumper::dump($standardProduct);

        return 0;
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
