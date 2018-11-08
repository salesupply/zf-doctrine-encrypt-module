<?php

namespace ZfDoctrineEncryptModule\Service;

use DoctrineEncrypt\Encryptors\EncryptorInterface;
use ParagonIE\Halite\HiddenString;

class EncryptManager
{
    /**
     * @var EncryptorInterface
     */
    protected $adapter;

    /**
     * EncryptManager constructor.
     *
     * @param EncryptorInterface $adapter
     */
    public function __construct(EncryptorInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public function encrypt(string $data): string
    {
        return $this->getAdapter()->encrypt($data);
    }

    /**
     * @param string $data
     *
     * @return HiddenString|string
     */
    public function decrypt(string $data)
    {
        return $this->getAdapter()->decrypt($data);
    }

    /**
     * @return EncryptorInterface
     */
    public function getAdapter(): EncryptorInterface
    {
        return $this->adapter;
    }

    /**
     * @param EncryptorInterface $adapter
     *
     * @return EncryptManager
     */
    public function setAdapter(EncryptorInterface $adapter): EncryptManager
    {
        $this->adapter = $adapter;

        return $this;
    }

}