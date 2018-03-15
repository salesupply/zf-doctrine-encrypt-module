<?php

namespace ZfDoctrineEncryptModule\Options;

use Doctrine\Common\Annotations\Reader;
use DoctrineEncrypt\Encryptors\EncryptorInterface;
use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var Reader|string
     */
    protected $reader;

    /**
     * @var EncryptorInterface|string
     */
    protected $adapter;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $salt;

    /**
     * @return Reader|string
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param Reader|string $reader
     * @return ModuleOptions
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
     * @return ModuleOptions
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
     * @return ModuleOptions
     */
    public function setKey(string $key): ModuleOptions
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     * @return ModuleOptions
     */
    public function setSalt(string $salt): ModuleOptions
    {
        $this->salt = $salt;
        return $this;
    }

}