<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoicecomments
 */
class Invoicecomments
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $invoicecommentId;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var integer
     */
    private $addedby;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $token;

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
     * Set comments
     *
     * @param string $comments
     * @return Invoicecomments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Invoicecomments
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
     * @return Invoicecomments
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
     * @return Invoicecomments
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
     * Set invoicecommentId
     *
     * @param integer $invoicecommentId
     * @return Invoicecomments
     */
    public function setInvoicecommentId($invoicecommentId)
    {
        $this->invoicecommentId = $invoicecommentId;

        return $this;
    }

    /**
     * Get invoicecommentId
     *
     * @return integer 
     */
    public function getInvoicecommentId()
    {
        return $this->invoicecommentId;
    }

    /**
     * Set addedby
     *
     * @param integer $addedby
     * @return Invoicecomments
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
     * Set token
     *
     * @param string $token
     * @return Invoicecomments
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
}
