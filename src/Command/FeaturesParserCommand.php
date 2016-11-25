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
class FeaturesParserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:parser:features')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $this->write($output, sprintf('Start downloading file <info>%s</info>', $filename));
        $downloader = $this->getContainer()->get('pim_icecat_connector.xml.downloader');
        $filepath = $downloader->download($filename, true);

        $this->write($output, sprintf('Start parsing file <info>%s</info>', $filepath));
        $streamer = XmlStringStreamer::createStringWalkerParser($filepath, [
            'captureDepth' => 4,
        ]);
        $normalizer = $this->getContainer()->get('pim_icecat_connector.xml.feature_normalizer');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository(Feature::class);
        $updater = new FeatureUpdater();

        while ($node = $streamer->getNode()) {
            $simpleXmlNode = simplexml_load_string($node);
            $feature = $normalizer->normalize($simpleXmlNode);
            $entity = $repository->find($feature['id']);
            if (null === $entity) {
                $entity=new Feature();
            }
            $updater->update($entity, $feature);
            $em->persist($entity);
        }
        $em->flush();
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
