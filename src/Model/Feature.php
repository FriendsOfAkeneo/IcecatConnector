<?php

namespace Pim\Bundle\IcecatConnectorBundle\Model;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Feature
{
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var int */
    private $class;

    /** @var int */
    private $measureId;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string[] */
    private $restrictedValues = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param int $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return int
     */
    public function getMeasureId()
    {
        return $this->measureId;
    }

    /**
     * @param int $measureId
     */
    public function setMeasureId($measureId)
    {
        $this->measureId = $measureId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string[]
     */
    public function getRestrictedValues()
    {
        return $this->restrictedValues;
    }

    /**
     * @param string[] $restrictedValues
     */
    public function setRestrictedValues($restrictedValues)
    {
        $this->restrictedValues = $restrictedValues;
    }

    /**
     * @param string $restrictedValue
     */
    public function addRestrictedValue($restrictedValue)
    {
        $this->restrictedValues[] = $restrictedValue;
    }
}
