<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * RevenueImages
 */
class RevenueImages
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    public $upltoken;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    public $path;

    /**
     * @var integer
     */
    private $status;

    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;

    /**
     * @var \DateTime
     */
    private $dateadded;

    /**
     * @var \DateTime
     */
    private $dateupdated;

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFileName()) {
            return;
        }

        $this->getFileName()->move($this->getUploadRootDir(), $this->path);

        /*$this->getFileName()->move(
            $this->getUploadRootDir(),
            $this->getFileName()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFileName()->getClientOriginalName();

        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }*/

        $this->fileName = null;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        // return __DIR__.'/../../../../web/'.$this->getUploadDir();
        return $_SERVER['DOCUMENT_ROOT'].'/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/imgs';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFileName()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFileName()->guessExtension();
        }
    }

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
     * Set fileName
     *
     * @param string $fileName
     * @return InvoiceImages
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;

        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            $this->path = 'initial';
        }

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return InvoiceImages
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
     * @return InvoiceImages
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
     * @return InvoiceImages
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
     * Set path
     *
     * @param string $path
     * @return InvoiceImages
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set upltoken
     *
     * @param string $upltoken
     * @return InvoiceImages
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
}
