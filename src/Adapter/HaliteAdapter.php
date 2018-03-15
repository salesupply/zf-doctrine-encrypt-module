<?php

namespace ZfDoctrineEncryptModule\Adapter;

use DoctrineEncrypt\Encryptors\EncryptorInterface;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\Halite\Util as CryptoUtil;

class HaliteAdapter implements EncryptorInterface
{
    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * @var string
     */
    private $salt;

    /**
     * HaliteAdapter constructor.
     * @param $key
     * @param $salt
     * @throws InvalidKey
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     */
    public function __construct($key, $salt)
    {
        if (CryptoUtil::safeStrlen($key) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {

            throw new InvalidKey(
                'Encryption key used for ' . __CLASS__ . ' must be exactly ' . \Sodium\CRYPTO_STREAM_KEYBYTES . ' characters long'
            );
        }

        if (CryptoUtil::safeStrlen($salt) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {

            throw new InvalidKey(
                'Salt used for ' . __CLASS__ . ' must be exactly ' . \Sodium\CRYPTO_STREAM_KEYBYTES . ' characters long'
            );
        }

        $this->setKey((new EncryptionKey((new HiddenString($key)))));
        $this->setSalt($salt);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data)
    {
        return Crypto::encrypt(new HiddenString($this->getSalt() . $data), $this->getKey());
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($data)
    {
        $decrypted = Crypto::decrypt($data, $this->getKey());

        return str_replace($this->getSalt(), '', $decrypted);
    }

    /**
     * @return EncryptionKey
     */
    public function getKey(): EncryptionKey
    {
        return $this->key;
    }

    /**
     * @param EncryptionKey $key
     * @return HaliteAdapter
     */
    public function setKey(EncryptionKey $key): HaliteAdapter
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
     * @return HaliteAdapter
     */
    public function setSalt(string $salt): HaliteAdapter
    {
        $this->salt = $salt;
        return $this;
    }

}