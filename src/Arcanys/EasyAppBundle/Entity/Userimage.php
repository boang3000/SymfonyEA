<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Userimage
 */
class Userimage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $userid;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $token;

    /**
     * @var integer
     */
    private $status;

    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;


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
     * Set userid
     *
     * @param integer $userid
     * @return Userimage
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return integer 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Userimage
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            $this->path = 'initial';
        }

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Userimage
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
     * Set status
     *
     * @param integer $status
     * @return Userimage
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

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFilename()) {
            return;
        }

        $this->getFilename()->move($this->getUploadRootDir(), $this->path);

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
     * Set token
     *
     * @param string $token
     * @return Userimage
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
