<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comments
 */
class Comments
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $idInvoice;

    /**
     * @var string
     */
    private $message;

    /**
     * @var integer
     */
    private $status;

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
     * Set idInvoice
     *
     * @param integer $idInvoice
     * @return Comments
     */
    public function setIdInvoice($idInvoice)
    {
        $this->idInvoice = $idInvoice;

        return $this;
    }

    /**
     * Get idInvoice
     *
     * @return integer 
     */
    public function getIdInvoice()
    {
        return $this->idInvoice;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Comments
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Comments
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
     * @return Comments
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
     * @return Comments
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
}
