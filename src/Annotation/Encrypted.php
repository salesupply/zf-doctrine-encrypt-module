<?php

namespace ZfDoctrineEncryptModule\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class Encrypted
 * @package ZfDoctrineEncryptModule\Annotation
 *
 * The below register the class as to be used as Doctrine's Annotation and only on class properties.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Encrypted
{
    /**
     * @var string linked property which implements \ZfDoctrineEncryptModule\Interfaces\SpicyInterface
     */
    public $spices;

    /**
     * @var string linked property which implements \ZfDoctrineEncryptModule\Interfaces\SaltInterface
     */
    public $salt;

    /**
     * @var string linked property which implements \ZfDoctrineEncryptModule\Interfaces\PepperInterface
     */
    public $pepper;

    /**
     * @return string
     */
    public function getSpices(): ?string
    {
        return $this->spices;
    }

    /**
     * @param string $spices
     * @return Encrypted
     */
    public function setSpices(?string $spices): Encrypted
    {
        $this->spices = $spices;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param null|string $salt
     * @return Encrypted
     */
    public function setSalt(?string $salt): Encrypted
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPepper(): ?string
    {
        return $this->pepper;
    }

    /**
     * @param null|string $pepper
     * @return Encrypted
     */
    public function setPepper(?string $pepper): Encrypted
    {
        $this->pepper = $pepper;
        return $this;
    }

}