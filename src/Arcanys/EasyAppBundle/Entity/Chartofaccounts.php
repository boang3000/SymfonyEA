<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chartofaccounts
 */
class Chartofaccounts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $chartname;

    /**
     * @var string
     */
    private $accountnumber;

    /**
     * @var string
     */
    private $accountname;

    /**
     * @var integer
     */
    private $addedby;

    /**
     * @var \DateTime
     */
    private $dateadded;

    /**
     * @var \DateTime
     */
    private $dateupdated;

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
     * Set chartname
     *
     * @param string $chartname
     * @return Chartofaccounts
     */
    public function setChartname($chartname)
    {
        $this->chartname = $chartname;

        return $this;
    }

    /**
     * Get chartname
     *
     * @return string 
     */
    public function getChartname()
    {
        return $this->chartname;
    }

    /**
     * Set addedby
     *
     * @param integer $addedby
     * @return Chartofaccounts
     */
    public function setAddedby($addedby)
    {
        $this->addedby = $addedby;

        return $this;
    }

    /**
     * Get addedby
     *
     * @return integer 
     */
    public function getAddedby()
    {
        return $this->addedby;
    }

    /**
     * Set dateadded
     *
     * @param \DateTime $dateadded
     * @return Chartofaccounts
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
     * @return Chartofaccounts
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
     * Set accountnumber
     *
     * @param string $accountnumber
     * @return Chartofaccounts
     */
    public function setAccountnumber($accountnumber)
    {
        $this->accountnumber = $accountnumber;

        return $this;
    }

    /**
     * Get accountnumber
     *
     * @return string 
     */
    public function getAccountnumber()
    {
        return $this->accountnumber;
    }

    /**
     * Set accountname
     *
     * @param string $accountname
     * @return Chartofaccounts
     */
    public function setAccountname($accountname)
    {
        $this->accountname = $accountname;

        return $this;
    }

    /**
     * Get accountname
     *
     * @return string 
     */
    public function getAccountname()
    {
        return $this->accountname;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Chartofaccounts
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
