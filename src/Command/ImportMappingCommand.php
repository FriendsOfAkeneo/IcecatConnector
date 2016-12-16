<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\IcecatConnectorBundle\Entity\Feature;
use Pim\Bundle\IcecatConnectorBundle\Updater\FeatureUpdater;
use Prewk\XmlStringStreamer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ImportMappingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:mapping:import')
//            ->addArgument(
//                'filename',
//                InputArgument::REQUIRED
//            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $filename = $input->getArgument('filename');
        $filepath = '/tmp/featuresList.csv';

        $this->write($output, sprintf('Start parsing file <info>%s</info>', $filepath));
        $normalizer = $this->getContainer()->get('pim_icecat_connector.xml.feature_normalizer');

        while ($node = $streamer->getNode()) {
            $simpleXmlNode = simplexml_load_string($node);
            $feature = $normalizer->normalize($simpleXmlNode);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function write(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }
}
