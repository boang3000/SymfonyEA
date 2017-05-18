<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoiceebankinfo
 */
class Invoiceebankinfo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $invoiceinfoId;

    /**
     * @var integer
     */
    private $entityId;

    /**
     * @var integer
     */
    private $entitybankinfoId;


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
     * Set invoiceinfoId
     *
     * @param integer $invoiceinfoId
     * @return Invoiceebankinfo
     */
    public function setInvoiceinfoId($invoiceinfoId)
    {
        $this->invoiceinfoId = $invoiceinfoId;

        return $this;
    }

    /**
     * Get invoiceinfoId
     *
     * @return integer 
     */
    public function getInvoiceinfoId()
    {
        return $this->invoiceinfoId;
    }

    /**
     * Set entitybankinfoId
     *
     * @param integer $entitybankinfoId
     * @return Invoiceebankinfo
     */
    public function setEntitybankinfoId($entitybankinfoId)
    {
        $this->entitybankinfoId = $entitybankinfoId;

        return $this;
    }

    /**
     * Get entitybankinfoId
     *
     * @return integer 
     */
    public function getEntitybankinfoId()
    {
        return $this->entitybankinfoId;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     * @return Invoiceebankinfo
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
