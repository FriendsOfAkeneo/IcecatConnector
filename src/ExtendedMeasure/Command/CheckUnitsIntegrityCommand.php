<?php

namespace Pim\Bundle\ExtendedMeasureBundle\Command;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check all yaml units definition files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CheckUnitsIntegrityCommand extends ContainerAwareCommand
{
    /** @var string[] */
    protected $errors;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:measures:check')
            ->setDescription('Checks measures defiition files');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->write($output, "<info>Start measures checks</info>");
        $measuresConfiguration = $this->getContainer()->getParameter('akeneo_measure.measures_config');

        $this->errors = [];

        foreach ($measuresConfiguration['measures_config'] as $family => $familyConfig) {
            $this->validateFamilyUnits($familyConfig['units'], $family);
        }

        foreach ($this->errors as $error) {
            $this->write($output, $error);
        }
    }

    /**
     * @param array  $unitsConfig
     * @param string $familyName
     */
    protected function validateFamilyUnits(array $unitsConfig, $familyName)
    {
        $repository = $this->getContainer()->get('pim_extended_measures.repository');
        foreach ($unitsConfig as $akeneoUnit => $unitConfig) {
            try {
                $repository->find($unitConfig['symbol']);
                if (isset($unitConfig['unece_code'])) {
                    $repository->find($unitConfig['unece_code']);
                }
                if (isset($unitConfig['alternative_symbols'])) {
                    foreach ($unitConfig['alternative_symbols'] as $symbol) {
                        $repository->find($symbol);
                    }
                }
            } catch (UnresolvableUnitException $e) {
                $this->errors[] = sprintf('%s -> %s: %s', $familyName, $akeneoUnit, $e->getMessage());
            } catch (UnknownUnitException $e) {
                $this->errors[] = sprintf('%s -> %s: %s', $familyName, $akeneoUnit, $e->getMessage());
            }
        }
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
