<?php

namespace Pim\Bundle\IcecatConnectorBundle\Command;

use Pim\Bundle\IcecatConnectorBundle\Exception\UnresolvableTypeException;
use Pim\Bundle\IcecatConnectorBundle\Mapper\AttributeTypeMapper;
use Pim\Bundle\IcecatConnectorBundle\Mapper\MeasureMapper;
use Pim\Bundle\IcecatConnectorBundle\Parser\FeatureToAttributeParser;
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
    /** @var OutputInterface */
    private $output;

    private $unresolvableTypes = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim-icecat:parser:features')
            ->addArgument(
                'filepath',
                InputArgument::REQUIRED
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
            'captureDepth' => 2,
        ]);

        $parser = new FeatureToAttributeParser();
        $measureMapper = new MeasureMapper($this->getContainer()->get('pim_extended_measures.resolver'));
        $attributeTypeMapper = new AttributeTypeMapper($measureMapper);

        while ($node = $streamer->getNode()) {
            try {
                $simpleXmlNode = simplexml_load_string($node);
                $feature = $parser->parseNode($simpleXmlNode);
                //dump($parser->normalize($feature));
                dump($attributeTypeMapper->resolvePimType($feature));
            } catch (UnresolvableTypeException $e) {
                $erroredFeature = $e->getFeature();
                if (!array_key_exists($erroredFeature->getType(), $this->unresolvableTypes)) {
                    $this->unresolvableTypes[$erroredFeature->getType()] = $e->getMessage();
                }
            }
        }
        dump($this->unresolvableTypes);
    }

    /**
     * @param string $message
     */
    private function write($message)
    {
        $this->output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }
}
