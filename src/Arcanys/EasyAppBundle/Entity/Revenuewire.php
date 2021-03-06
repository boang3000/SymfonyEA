<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Revenuewire
 */
class Revenuewire
{
    /**
     * @var integer
     */
    private $id;
	
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
     * @var integer
     */
    private $wiretype;
	
	/**
     * @var integer
     */
    private $status;

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
     * @return Revenuewire
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
     * @return Revenuewire
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
     * @return Revenuewire
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
     * Set wiretype
     *
     * @param integer $wiretype
     * @return Revenuewire
     */
    public function setWiretype($wiretype)
    {
        $this->wiretype = $wiretype;

        return $this;
    }

    /**
     * Get wiretype
     *
     * @return integer 
     */
    public function getWiretype()
    {
        return $this->wiretype;
    }
	
	/**
     * Set status
     *
     * @param integer $status
     * @return Revenuewire
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set description
     *
     * @param integer $description
     * @return Revenuewire
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
     * @return Revenuewire
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
     * @return Revenuewire
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
     * @return Revenuewire
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
