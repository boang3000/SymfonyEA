<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 */
class Entity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $bankName;

    /**
     * @var string
     */
    private $bankContact;

    /**
     * @var string
     */
    private $bankAcct;

    /**
     * @var float
     */
    private $startBalance;

    /**
     * @var float
     */
    private $curBalance;

    /**
     * @var float
     */
    private $routingBalance;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $entityAddress;

    /**
     * @var string
     */
    private $entityEmailaddress;

    /**
     * @var string
     */
    private $entityContact;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $company;

    /**
     * @var \DateTime
     */
    private $dateadded;

    /**
     * @var \DateTime
     */
    private $dateupdated;


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
     * Set entityName
     *
     * @param string $entityName
     * @return Entity
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get entityName
     *
     * @return string 
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set bank
     *
     * @param string $bank
     * @return Entity
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string 
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set bankAcct
     *
     * @param integer $bankAcct
     * @return Entity
     */
    public function setBankAcct($bankAcct)
    {
        $this->bankAcct = $bankAcct;

        return $this;
    }

    /**
     * Get bankAcct
     *
     * @return integer 
     */
    public function getBankAcct()
    {
        return $this->bankAcct;
    }

    /**
     * Set startBalance
     *
     * @param float $startBalance
     * @return Entity
     */
    public function setStartBalance($startBalance)
    {
        $this->startBalance = $startBalance;

        return $this;
    }

    /**
     * Get startBalance
     *
     * @return float 
     */
    public function getStartBalance()
    {
        return $this->startBalance;
    }

    /**
     * Set curBalance
     *
     * @param float $curBalance
     * @return Entity
     */
    public function setCurBalance($curBalance)
    {
        $this->curBalance = $curBalance;

        return $this;
    }

    /**
     * Get curBalance
     *
     * @return float 
     */
    public function getCurBalance()
    {
        return $this->curBalance;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Entity
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
     * Set dateadded
     *
     * @param \DateTime $dateadded
     * @return Entity
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
     * Set dateupdated
     *
     * @param \DateTime $dateupdated
     * @return Entity
     */
    public function setDateupdated($dateupdated)
    {
        $this->dateupdated = $dateupdated;

        return $this;
    }

    /**
     * Get dateupdated
     *
     * @return \DateTime 
     */
    public function getDateupdated()
    {
        return $this->dateupdated;
    }
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $bank;



    /**
     * Set bankName
     *
     * @param string $bankName
     * @return Entity
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string 
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set bankContact
     *
     * @param string $bankContact
     * @return Entity
     */
    public function setBankContact($bankContact)
    {
        $this->bankContact = $bankContact;

        return $this;
    }

    /**
     * Get bankContact
     *
     * @return string 
     */
    public function getBankContact()
    {
        return $this->bankContact;
    }

    /**
     * Set routingBalance
     *
     * @param float $routingBalance
     * @return Entity
     */
    public function setRoutingBalance($routingBalance)
    {
        $this->routingBalance = $routingBalance;

        return $this;
    }

    /**
     * Get routingBalance
     *
     * @return float 
     */
    public function getRoutingBalance()
    {
        return $this->routingBalance;
    }

    public function prePersist()
    {
        if(!$this->dateadded) {
            $this->setDateadded(new \DateTime);
        }
    }

    public function preUpdate()
    {
        if(!$this->dateupdated) {
            $this->setDateupdated(new \DateTime);
        }
    }
	
	/**
    * (For the FormType entity)
    *
    * @return string String representation of this class
    */
    public function __toString()
    {
        return $this->getEntityName();
    }

    /**
     * Set entity_address
     *
     * @param string $entityAddress
     * @return Entity
     */
    public function setEntityAddress($entityAddress)
    {
        $this->entityAddress = $entityAddress;

        return $this;
    }

    /**
     * Get entity_address
     *
     * @return string 
     */
    public function getEntityAddress()
    {
        return $this->entityAddress;
    }

    /**
     * Set entity_emailaddress
     *
     * @param string $entityEmailaddress
     * @return Entity
     */
    public function setEntityEmailaddress($entityEmailaddress)
    {
        $this->entityEmailaddress = $entityEmailaddress;

        return $this;
    }

    /**
     * Get entity_emailaddress
     *
     * @return string 
     */
    public function getEntityEmailaddress()
    {
        return $this->entityEmailaddress;
    }

    /**
     * Set entity_contact
     *
     * @param string $entityContact
     * @return Entity
     */
    public function setEntityContact($entityContact)
    {
        $this->entityContact = $entityContact;

        return $this;
    }

    /**
     * Get entity_contact
     *
     * @return string 
     */
    public function getEntityContact()
    {
        return $this->entityContact;
    }



    /**
     * Set token
     *
     * @param string $token
     * @return Entity
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Entity
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
