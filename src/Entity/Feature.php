<?php

namespace Pim\Bundle\IcecatConnectorBundle\Entity;

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
    private $name;

    /** @var string */
    private $type;

    /** @var string */
    private $description;

    /** @var string */
    private $sign;

    /** @var string */
    private $pimCode;

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
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param string $sign
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
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
     * @return string
     */
    public function getPimCode()
    {
        return $this->pimCode;
    }

    /**
     * @param string $pimCode
     */
    public function setPimCode($pimCode)
    {
        $this->pimCode = $pimCode;
    }
}
