<?php

namespace ZfDoctrineEncryptModule\Options;

use Doctrine\Common\Annotations\Reader;
use DoctrineEncrypt\Encryptors\EncryptorInterface;
use Zend\Stdlib\AbstractOptions;
use ZfDoctrineEncryptModule\Interfaces\HashInterface;

class HashModuleOptions extends AbstractOptions
{
    /**
     * @var Reader|string
     */
    protected $reader;

    /**
     * @var HashInterface|string
     */
    protected $adapter;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $pepper;

    /**
     * @return Reader|string
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param Reader|string $reader
     * @return HashModuleOptions
     */
    public function setReader($reader)
    {
        $this->reader = $reader;
        return $this;
    }

    /**
     * @return EncryptorInterface|string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param EncryptorInterface|string $adapter
     * @return HashModuleOptions
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return HashModuleOptions
     */
    public function setKey(string $key): HashModuleOptions
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getPepper(): string
    {
        return $this->pepper;
    }

    /**
     * @param string $pepper
     * @return HashModuleOptions
     */
    public function setPepper(string $pepper): HashModuleOptions
    {
        $this->pepper = $pepper;
        return $this;
    }

}