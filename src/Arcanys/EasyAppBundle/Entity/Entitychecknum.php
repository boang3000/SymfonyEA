<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entitychecknum
 */
class Entitychecknum
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $chkInvoiceid;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $checknum;


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
     * Set chkInvoiceid
     *
     * @param integer $chkInvoiceid
     * @return Entitychecknum
     */
    public function setChkInvoiceid($chkInvoiceid)
    {
        $this->chkInvoiceid = $chkInvoiceid;

        return $this;
    }

    /**
     * Get chkInvoiceid
     *
     * @return integer 
     */
    public function getChkInvoiceid()
    {
        return $this->chkInvoiceid;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Entitychecknum
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
     * Set checknum
     *
     * @param string $checknum
     * @return Entitychecknum
     */
    public function setChecknum($checknum)
    {
        $this->checknum = $checknum;

        return $this;
    }

    /**
     * Get checknum
     *
     * @return string 
     */
    public function getChecknum()
    {
        return $this->checknum;
    }
}
