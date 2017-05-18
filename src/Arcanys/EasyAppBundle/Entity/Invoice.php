<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoice
 */
class Invoice
{
    /**
     * @var integer
     */
    private $invoiceId;

    /**
     * @var integer
     */
    private $idEntity;

    /**
     * @var integer
     */
    private $idVendor;

    /**
     * @var string
     */
    private $checkNo;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var integer
     */
    private $bankinfo;

    /**
     * @var integer
     */
    private $managerApproval;

    /**
     * @var integer
     */
    private $chartOfAccounts;

    /**
     * @var string
     */
    private $dueDate;

    /**
     * @var string
     */
    private $invoicedate;

    /**
     * @var string
     */
    private $invoicenumber;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var integer
     */
    private $deletestatus;

    /**
     * @var integer
     */
    private $readstatus;

    /**
     * @var integer
     */
    private $addedby;

    /**
     * @var float
     */
    private $outstandingbalance;

    /**
     * @var float
     */
    private $remainingbalance;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $formtoken;

    /**
     * @var integer
     */
    private $statusdraft;

    /**
     * @var integer
     */
    private $pagenumber;

    /**
     * @var integer
     */
    private $printready;

    /**
     * @var integer
     */
    private $entityready;

    /**
     * @var integer
     */
    private $assigned;

    /**
     * @var \DateTime
     */
    private $dateadded;

    /**
     * @var \DateTime
     */
    private $dateupdated;

    /**
     * @var \DateTime
     */
    private $sentAt;


    /**
     * Get invoiceId
     *
     * @return integer 
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * Set idEntity
     *
     * @param integer $idEntity
     * @return Invoice
     */
    public function setIdEntity($idEntity)
    {
        $this->idEntity = $idEntity;

        return $this;
    }

    /**
     * Get idEntity
     *
     * @return integer 
     */
    public function getIdEntity()
    {
        return $this->idEntity;
    }

    /**
     * Set idVendor
     *
     * @param integer $idVendor
     * @return Invoice
     */
    public function setIdVendor($idVendor)
    {
        $this->idVendor = $idVendor;

        return $this;
    }

    /**
     * Get idVendor
     *
     * @return integer 
     */
    public function getIdVendor()
    {
        return $this->idVendor;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Invoice
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
     * Set status
     *
     * @param integer $status
     * @return Invoice
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
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Invoice
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Invoice
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
     * Set dateadded
     *
     * @param \DateTime $dateadded
     * @return Invoice
     */
    public function setDateadded($dateadded)
    {
        $this->dateadded = $dateadded;
        $this->dateupdated = $dateadded;

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
     * @return Invoice
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
     * Set checkNo
     *
     * @param integer $checkNo
     * @return Invoice
     */
    public function setCheckNo($checkNo)
    {
        $this->checkNo = $checkNo;

        return $this;
    }

    /**
     * Get checkNo
     *
     * @return integer 
     */
    public function getCheckNo()
    {
        return $this->checkNo;
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
     * Set managerApproval
     *
     * @param integer $managerApproval
     * @return Invoice
     */
    public function setManagerApproval($managerApproval)
    {
        $this->managerApproval = $managerApproval;

        return $this;
    }

    /**
     * Get managerApproval
     *
     * @return integer 
     */
    public function getManagerApproval()
    {
        return $this->managerApproval;
    }

    /**
     * Set chartOfAccounts
     *
     * @param integer $chartOfAccounts
     * @return Invoice
     */
    public function setChartOfAccounts($chartOfAccounts)
    {
        $this->chartOfAccounts = $chartOfAccounts;

        return $this;
    }

    /**
     * Get chartOfAccounts
     *
     * @return integer 
     */
    public function getChartOfAccounts()
    {
        return $this->chartOfAccounts;
    }

    /**
     * Set addedby
     *
     * @param integer $addedby
     * @return Invoice
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
     * Set deletestatus
     *
     * @param integer $deletestatus
     * @return Invoice
     */
    public function setDeletestatus($deletestatus)
    {
        $this->deletestatus = $deletestatus;

        return $this;
    }

    /**
     * Get deletestatus
     *
     * @return integer 
     */
    public function getDeletestatus()
    {
        return $this->deletestatus;
    }

    /**
     * Set readstatus
     *
     * @param integer $readstatus
     * @return Invoice
     */
    public function setReadstatus($readstatus)
    {
        $this->readstatus = $readstatus;

        return $this;
    }

    /**
     * Get readstatus
     *
     * @return integer 
     */
    public function getReadstatus()
    {
        return $this->readstatus;
    }

    /**
     * Set invoicedate
     *
     * @param string $invoicedate
     * @return Invoice
     */
    public function setInvoicedate($invoicedate)
    {
        $this->invoicedate = $invoicedate;

        return $this;
    }

    /**
     * Get invoicedate
     *
     * @return string 
     */
    public function getInvoicedate()
    {
        return $this->invoicedate;
    }

    /**
     * Set invoicenumber
     *
     * @param integer $invoicenumber
     * @return Invoice
     */
    public function setInvoicenumber($invoicenumber)
    {
        $this->invoicenumber = $invoicenumber;

        return $this;
    }

    /**
     * Get invoicenumber
     *
     * @return integer 
     */
    public function getInvoicenumber()
    {
        return $this->invoicenumber;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Invoice
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
     * Set outstandingbalance
     *
     * @param integer $outstandingbalance
     * @return Invoice
     */
    public function setOutstandingbalance($outstandingbalance)
    {
        $this->outstandingbalance = $outstandingbalance;

        return $this;
    }

    /**
     * Get outstandingbalance
     *
     * @return integer 
     */
    public function getOutstandingbalance()
    {
        return $this->outstandingbalance;
    }

    /**
     * Set statusdraft
     *
     * @param integer $statusdraft
     * @return Invoice
     */
    public function setStatusdraft($statusdraft)
    {
        $this->statusdraft = $statusdraft;

        return $this;
    }

    /**
     * Get statusdraft
     *
     * @return integer 
     */
    public function getStatusdraft()
    {
        return $this->statusdraft;
    }

    /**
     * Set pagenumber
     *
     * @param integer $pagenumber
     * @return Invoice
     */
    public function setPagenumber($pagenumber)
    {
        $this->pagenumber = $pagenumber;

        return $this;
    }

    /**
     * Get pagenumber
     *
     * @return integer 
     */
    public function getPagenumber()
    {
        return $this->pagenumber;
    }

    /**
     * Set formtoken
     *
     * @param string $formtoken
     * @return Invoice
     */
    public function setFormtoken($formtoken)
    {
        $this->formtoken = $formtoken;

        return $this;
    }

    /**
     * Get formtoken
     *
     * @return string 
     */
    public function getFormtoken()
    {
        return $this->formtoken;
    }

    /**
     * Set printready
     *
     * @param integer $printready
     * @return Invoice
     */
    public function setPrintready($printready)
    {
        $this->printready = $printready;

        return $this;
    }

    /**
     * Get printready
     *
     * @return integer 
     */
    public function getPrintready()
    {
        return $this->printready;
    }

    /**
     * Set entityready
     *
     * @param integer $entityready
     * @return Invoice
     */
    public function setEntityready($entityready)
    {
        $this->entityready = $entityready;

        return $this;
    }

    /**
     * Get entityready
     *
     * @return integer 
     */
    public function getEntityready()
    {
        return $this->entityready;
    }

    /**
     * Set bankinfo
     *
     * @param integer $bankinfo
     * @return Invoice
     */
    public function setBankinfo($bankinfo)
    {
        $this->bankinfo = $bankinfo;

        return $this;
    }

    /**
     * Get bankinfo
     *
     * @return integer 
     */
    public function getBankinfo()
    {
        return $this->bankinfo;
    }

    /**
     * Set remainingbalance
     *
     * @param float $remainingbalance
     * @return Invoice
     */
    public function setRemainingbalance($remainingbalance)
    {
        $this->remainingbalance = $remainingbalance;

        return $this;
    }

    /**
     * Get remainingbalance
     *
     * @return float 
     */
    public function getRemainingbalance()
    {
        return $this->remainingbalance;
    }

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     * @return Invoice
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set assigned
     *
     * @param integer $assigned
     * @return Invoice
     */
    public function setAssigned($assigned)
    {
        $this->assigned = $assigned;

        return $this;
    }

    /**
     * Get assigned
     *
     * @return integer 
     */
    public function getAssigned()
    {
        return $this->assigned;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Invoice
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
