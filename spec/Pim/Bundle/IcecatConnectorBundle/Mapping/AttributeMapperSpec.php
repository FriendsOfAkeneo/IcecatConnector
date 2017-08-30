<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Mapping;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Exception\MapperException;
use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;

class AttributeMapperSpec extends ObjectBehavior
{
    /** @var string */
    private $mappingFilePath = '/tmp/mapping.csv';

    /** @var array */
    private $mapping = [
        ['foo', 'bar', '1'],
        ['bar', 'baz', '0'],
    ];

    function let(ConfigManager $configManager)
    {
        $headers = ['feature_id', 'pim_attribute_code', 'ignore_flag'];

        $fp = fopen($this->mappingFilePath, 'w');
        fputcsv($fp, $headers, ';');
        foreach ($this->mapping as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);
        $this->beConstructedWith($configManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeMapper::class);
    }

    function it_throws_exception_on_unexisting_mapping_file()
    {
        unlink($this->mappingFilePath);
        $this->shouldThrow(MapperException::class)->during('getMapped', ['foo']);
    }

    function it_returns_mapped_attribute()
    {
        foreach ($this->mapping as $fields) {
            $this->getMapped($fields[0])->shouldReturn($fields[1]);
        }
    }

    function it_returns_null_on_unfound_attribute()
    {
        $this->getMapped('notExistingCode')->shouldReturn(null);
    }
}
