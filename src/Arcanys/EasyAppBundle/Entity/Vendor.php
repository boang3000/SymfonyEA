<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vendor
 */
class Vendor
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var integer
     */
    private $zip;

    /**
     * @var string
     */
    private $contactPerson;

    /**
     * @var string
     */
    private $phoneNum;

    /**
     * @var string
     */
    private $localNum;

    /**
     * @var string
     */
    private $email;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var integer
     */
    private $taxform;

    /**
     * @var integer
     */
    private $w9form;

    /**
     * @var integer
     */
    private $paymentterm;

    /**
     * @var integer
    */
    private $acctnumber;

    /**
     * @var integer
     */
    private $chartsofaccounts;

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
     * Set name
     *
     * @param string $name
     * @return Vendor
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
     * Set address
     *
     * @param string $address
     * @return Vendor
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set contactPerson
     *
     * @param integer $contactPerson
     * @return Vendor
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    /**
     * Get contactPerson
     *
     * @return integer 
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Set phoneNum
     *
     * @param integer $phoneNum
     * @return Vendor
     */
    public function setPhoneNum($phoneNum)
    {
        $this->phoneNum = $phoneNum;

        return $this;
    }

    /**
     * Get phoneNum
     *
     * @return integer 
     */
    public function getPhoneNum()
    {
        return $this->phoneNum;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Vendor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Vendor
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
     * @return Vendor
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
     * @return Vendor
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
     * Set localNum
     *
     * @param integer $localNum
     * @return Vendor
     */
    public function setLocalNum($localNum)
    {
        $this->localNum = $localNum;

        return $this;
    }

    /**
     * Get localNum
     *
     * @return integer 
     */
    public function getLocalNum()
    {
        return $this->localNum;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Vendor
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Vendor
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zip
     *
     * @param integer $zip
     * @return Vendor
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return integer 
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set taxform
     *
     * @param integer $taxform
     * @return Vendor
     */
    public function setTaxform($taxform)
    {
        $this->taxform = $taxform;

        return $this;
    }

    /**
     * Get taxform
     *
     * @return integer 
     */
    public function getTaxform()
    {
        return $this->taxform;
    }

    /**
     * Set w9form
     *
     * @param integer $w9form
     * @return Vendor
     */
    public function setW9form($w9form)
    {
        $this->w9form = $w9form;

        return $this;
    }

    /**
     * Get w9form
     *
     * @return integer 
     */
    public function getW9form()
    {
        return $this->w9form;
    }

    /**
     * Set paymentterm
     *
     * @param integer $paymentterm
     * @return Vendor
     */
    public function setPaymentterm($paymentterm)
    {
        $this->paymentterm = $paymentterm;

        return $this;
    }

    /**
     * Get paymentterm
     *
     * @return integer 
     */
    public function getPaymentterm()
    {
        return $this->paymentterm;
    }

    /**
     * Set acctnumber
     *
     * @param integer $acctnumber
     * @return Vendor
     */
    public function setAcctnumber($acctnumber)
    {
        $this->acctnumber = $acctnumber;

        return $this;
    }

    /**
     * Get acctnumber
     *
     * @return integer 
     */
    public function getAcctnumber()
    {
        return $this->acctnumber;
    }

    /**
     * Set chartsofaccounts
     *
     * @param integer $chartsofaccounts
     * @return Vendor
     */
    public function setChartsofaccounts($chartsofaccounts)
    {
        $this->chartsofaccounts = $chartsofaccounts;

        return $this;
    }

    /**
     * Get chartsofaccounts
     *
     * @return integer 
     */
    public function getChartsofaccounts()
    {
        return $this->chartsofaccounts;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Vendor
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
