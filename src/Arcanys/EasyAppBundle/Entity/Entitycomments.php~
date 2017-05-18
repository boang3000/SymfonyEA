<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entitycomments
 */
class Entitycomments
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $entityId;

    /**
     * @var string
     */
    private $comments;

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
     * Set entityId
     *
     * @param integer $entityId
     * @return Entitycomments
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

    /**
     * Set comments
     *
     * @param string $comments
     * @return Entitycomments
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
     * @return Entitycomments
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
     * @return Entitycomments
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
     * @return Entitycomments
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
}
