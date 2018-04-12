<?php

namespace ZfDoctrineEncryptModule\Adapter;

use DoctrineEncrypt\Encryptors\EncryptorInterface;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;

class HaliteEncryptionAdapter implements EncryptorInterface
{
    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * HaliteAdapter constructor.
     * @param $key
     * @throws InvalidKey
     * @throws \TypeError
     */
    public function __construct($key)
    {
        if (Binary::safeStrlen($key) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {

            throw new InvalidKey(
                'Encryption key used for ' . __CLASS__ . '::' . __FUNCTION__ . ' must be exactly ' . \Sodium\CRYPTO_STREAM_KEYBYTES . ' characters long'
            );
        }

        $this->setKey((new EncryptionKey((new HiddenString($key)))));
    }

    /**
     * @param string $data
     * @return string
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function encrypt($data)
    {
        return Crypto::encrypt(new HiddenString($data), $this->getKey());
    }

    /**
     * @param string $data
     * @return HiddenString|string
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function decrypt($data)
    {
        return Crypto::decrypt($data, $this->getKey());
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
     * @return HaliteEncryptionAdapter
     */
    public function setKey(EncryptionKey $key): HaliteEncryptionAdapter
    {
        $this->key = $key;
        return $this;
    }
}