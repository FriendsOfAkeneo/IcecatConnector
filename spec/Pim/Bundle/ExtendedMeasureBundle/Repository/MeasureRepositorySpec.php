<?php

namespace spec\Pim\Bundle\ExtendedMeasureBundle\Repository;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Repository\MeasureRepositoryInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasureRepositorySpec extends ObjectBehavior
{
    public function let()
    {
        $config = require(__DIR__ . '/../Resources/measures/merged_configuration.php');
        $this->beConstructedWith($config);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(MeasureRepositoryInterface::class);
    }

    public function it_returns_measure_from_a_unit()
    {
        $this
            ->find('KILOGRAM')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1000]],
                    'symbol'              => 'kg',
                    'name'                => 'kilo gram',
                    'unece_code'          => 'KGM',
                    'alternative_symbols' => ['kilo'],
                    'family'              => 'Weight',
                    'unit'                => 'KILOGRAM',
                ]
            );
    }


    public function it_returns_measure_from_a_symbol()
    {
        $this
            ->find('kg')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1000]],
                    'symbol'              => 'kg',
                    'name'                => 'kilo gram',
                    'unece_code'          => 'KGM',
                    'alternative_symbols' => ['kilo'],
                    'family'              => 'Weight',
                    'unit'                => 'KILOGRAM',
                ]
            );
        $this
            ->find('kilo')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1000]],
                    'symbol'              => 'kg',
                    'name'                => 'kilo gram',
                    'unece_code'          => 'KGM',
                    'alternative_symbols' => ['kilo'],
                    'family'              => 'Weight',
                    'unit'                => 'KILOGRAM',
                ]
            );
        $this
            ->find('mt')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1]],
                    'symbol'              => 'm',
                    'alternative_symbols' => ['mt'],
                    'family'              => 'Length',
                    'unit'                => 'METER',
                ]
            );
    }

    public function it_returns_measure_from_unece_code()
    {
        $this
            ->find('KGM')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1000]],
                    'symbol'              => 'kg',
                    'name'                => 'kilo gram',
                    'unece_code'          => 'KGM',
                    'alternative_symbols' => ['kilo'],
                    'family'              => 'Weight',
                    'unit'                => 'KILOGRAM',
                ]
            );
    }

    public function it_throws_an_exception_for_unknown_unit()
    {
        $this
            ->shouldThrow(
                new UnknownUnitException('FOO_UNIT')
            )
            ->during('find', ['FOO_UNIT']);
    }

    public function it_throws_an_exception_for_unknown_symbol()
    {
        $this
            ->shouldThrow(
                new UnknownUnitException('parsec')
            )
            ->during('find', ['parsec']);
    }

    public function it_finds_unit_in_a_family()
    {
        $this
            ->find('DUPLICATE_UNIT', 'Length')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 666]],
                    'symbol'              => 'foo',
                    'alternative_symbols' => [],
                    'family'              => 'Length',
                    'unit'                => 'DUPLICATE_UNIT',
                ]
            );
    }

    public function it_finds_symbol_in_a_family()
    {
        $this
            ->find('m', 'Length')
            ->shouldReturn(
                [
                    'convert'             => [['mul' => 1]],
                    'symbol'              => 'm',
                    'alternative_symbols' => ['mt'],
                    'family'              => 'Length',
                    'unit'                => 'METER',
                ]
            );
    }

    public function it_throws_an_exception_for_unresolvable_unit()
    {
        $message = 'Unable to resolve the unit "DUPLICATE_UNIT" in [family: Weight, unit: DUPLICATE_UNIT] [family: Length, unit: DUPLICATE_UNIT]';
        $this
            ->shouldThrow(
                new UnresolvableUnitException($message)
            )
            ->during('find', ['DUPLICATE_UNIT']);
    }

    public function it_throws_an_exception_for_unresolvable_symbol()
    {
        $message = 'Unable to resolve the unit "m" in [family: Weight, unit: BADMETER] [family: Length, unit: METER]';
        $this
            ->shouldThrow(
                new UnresolvableUnitException($message)
            )
            ->during('find', ['m']);
    }
}
