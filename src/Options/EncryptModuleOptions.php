<?php

namespace ZfDoctrineEncryptModule\Options;

use Doctrine\Common\Annotations\Reader;
use DoctrineEncrypt\Encryptors\EncryptorInterface;
use Zend\Stdlib\AbstractOptions;

class EncryptModuleOptions extends AbstractOptions
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
     * @return Reader|string
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param Reader|string $reader
     * @return EncryptModuleOptions
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
     * @return EncryptModuleOptions
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
     * @return EncryptModuleOptions
     */
    public function setKey(string $key): EncryptModuleOptions
    {
        $this->key = $key;
        return $this;
    }
}