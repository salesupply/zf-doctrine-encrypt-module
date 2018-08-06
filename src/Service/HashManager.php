<?php

namespace ZfDoctrineEncryptModule\Service;

use ZfDoctrineEncryptModule\Interfaces\HashInterface;

class HashManager
{
    /**
     * @var HashInterface
     */
    protected $adapter;

    /**
     * HashManager constructor.
     *
     * @param HashInterface $adapter
     */
    public function __construct(HashInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function hash(string $password)
    {
        return $this->getAdapter()->hash($password);
    }

    /**
     * @param string $string
     * @param string $storedString
     *
     * @return bool
     */
    public function verify(string $string, string $storedString)
    {
        return $this->getAdapter()->verify($string, $storedString);
    }

    /**
     * @return HashInterface
     */
    public function getAdapter() : HashInterface
    {
        return $this->adapter;
    }

    /**
     * @param HashInterface $adapter
     *
     * @return HashManager
     */
    public function setAdapter(HashInterface $adapter) : HashManager
    {
        $this->adapter = $adapter;

        return $this;
    }

}