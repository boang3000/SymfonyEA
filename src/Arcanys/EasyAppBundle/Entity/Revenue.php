<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Revenue
 */
class Revenue
{
    /**
     * @var integer
     */
    public $id;
	
	/**
     * @var \DateTime
     */
    private $dateadded;
	
    /**
    * @var string
    */
    private $entityId;

    /**
     * @var integer
     */
    private $entitybanknameId;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $description;
	
	/**
     * @var string
     */
    private $upltoken;

    /**
     * @var string
     */
    private $company;
	
	
	/**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateadded
     *
     * @param \DateTime $dateadded
     * @return Revenue
     */
    public function setDateadded($dateadded)
    {
        $this->dateadded = $dateadded;

        return $this;
    }

    /**
     * Get dateadded
     *
     * @return \DateTime 
     */
    public function getDateadded()
    {
        return $this->dateadded;
    }

    /**
     * Set entityId
     *
     * @param string $entityId
     * @return Revenue
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return string 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Revenue
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set description
     *
     * @param integer $description
     * @return Revenue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
	
	/**
     * Set upltoken
     *
     * @param integer $upltoken
     * @return Revenue
     */
    public function setUpltoken($upltoken)
    {
        $this->upltoken = $upltoken;

        return $this;
    }

    /**
     * Get upltoken
     *
     * @return string 
     */
    public function getUpltoken()
    {
        return $this->upltoken;
    }
	

    /**
     * Set entitybanknameId
     *
     * @param integer $entitybanknameId
     * @return Revenue
     */
    public function setEntitybanknameId($entitybanknameId)
    {
        $this->entitybanknameId = $entitybanknameId;

        return $this;
    }

    /**
     * Get entitybanknameId
     *
     * @return integer 
     */
    public function getEntitybanknameId()
    {
        return $this->entitybanknameId;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Revenue
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }
}
