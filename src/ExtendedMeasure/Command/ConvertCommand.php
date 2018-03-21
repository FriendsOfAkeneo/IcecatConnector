<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Command;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converts a number from a unit to another one
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ConvertCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:measures:convert')
            ->setDescription('Convert a number from a unit to another one')
            ->addArgument('family', InputArgument::REQUIRED, 'The conversion family')
            ->addArgument('number', InputArgument::REQUIRED, 'The number to convert')
            ->addArgument('base_unit', InputArgument::REQUIRED, 'The base unit')
            ->addArgument('conversion_unit', InputArgument::REQUIRED, 'The unit in which you want to convert')
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $family = $input->getArgument('family');
        $number = $input->getArgument('number');
        $baseUnit = $input->getArgument('base_unit');
        $conversionUnit = $input->getArgument('conversion_unit');

        $measureConverter = $this->getMeasureConverter();
        $measureConverter->setFamily($input->getArgument($family));
        $result = $measureConverter->convert($baseUnit, $conversionUnit, $number);

        $output->writeln(sprintf('<info>%s %s = %s %s</info>', $number, $baseUnit, $result, $conversionUnit));
    }

    /**
     * @return MeasureConverter
     */
    protected function getMeasureConverter()
    {
        return $this->getContainer()->get('akeneo_measure.measure_converter');
    }
}
