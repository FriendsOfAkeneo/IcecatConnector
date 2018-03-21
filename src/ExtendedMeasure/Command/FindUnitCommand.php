<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Find PIM unit command
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class FindUnitCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:measures:find')
            ->setDescription('Find a PIM unit.')
            ->addOption(
                'symbol',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'unit',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'family',
                null,
                InputOption::VALUE_OPTIONAL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symbol = $input->getOption('symbol');
        $unit = $input->getOption('unit');
        $family = $input->getOption('family');

        $repository = $this->getContainer()->get('pim_extended_measures.repository');

        if (null !== $unit) {
            $this->write($output, sprintf('Search for unit <info>%s</info>', $unit));
            $measure = $repository->find($unit, $family);
        } elseif (null !== $symbol) {
            $this->write($output, sprintf('Search for symbol <info>%s</info>', $symbol));
            $measure = $repository->find($symbol, $family);
        } else {
            throw new \InvalidArgumentException('You must search a symbol or a family.');
        }

        $this->write($output, sprintf('Family = <info>%s</info>', $measure['family']));
        $this->write($output, sprintf('Unit = <info>%s</info>', $measure['unit']));
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
