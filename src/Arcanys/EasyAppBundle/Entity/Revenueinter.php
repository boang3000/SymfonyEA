<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Revenueinter
 */
class Revenueinter
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
    private $entityIdFrom;

    /**
     * @var integer
     */
    private $entitybanknameIdFrom;
	
	/**
     * @var string
     */
    private $entityIdTo;

    /**
     * @var integer
     */
    private $entitybanknameIdTo;

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
     * Set entityIdFrom
     *
     * @param string $entityIdFrom
     * @return Revenuewire
     */
    public function setEntityIdFrom($entityIdFrom)
    {
        $this->entityIdFrom = $entityIdFrom;

        return $this;
    }

    /**
     * Get entityIdFrom
     *
     * @return string 
     */
    public function getEntityIdFrom()
    {
        return $this->entityIdFrom;
    }
	
	/**
     * Set entityIdTo
     *
     * @param string $entityIdTo
     * @return Revenuewire
     */
    public function setEntityIdTo($entityIdTo)
    {
        $this->entityIdTo = $entityIdTo;

        return $this;
    }

    /**
     * Get entityIdTo
     *
     * @return string 
     */
    public function getEntityIdTo()
    {
        return $this->entityIdTo;
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
     * Set entitybanknameIdFrom
     *
     * @param integer $entitybanknameIdFrom
     * @return Revenueinter
     */
    public function setEntitybanknameIdFrom($entitybanknameIdFrom)
    {
        $this->entitybanknameIdFrom = $entitybanknameIdFrom;

        return $this;
    }

    /**
     * Get entitybanknameIdFrom
     *
     * @return integer 
     */
    public function getEntitybanknameIdFrom()
    {
        return $this->entitybanknameIdFrom;
    }

    /**
     * Set entitybanknameIdTo
     *
     * @param integer $entitybanknameIdTo
     * @return Revenueinter
     */
    public function setEntitybanknameIdTo($entitybanknameIdTo)
    {
        $this->entitybanknameIdTo = $entitybanknameIdTo;

        return $this;
    }

    /**
     * Get entitybanknameIdTo
     *
     * @return integer 
     */
    public function getEntitybanknameIdTo()
    {
        return $this->entitybanknameIdTo;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Revenueinter
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
