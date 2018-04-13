<?php

namespace ZfDoctrineEncryptModule\Adapter;

use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Password;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ZfDoctrineEncryptModule\Interfaces\HashInterface;

class HaliteHashingAdapter implements HashInterface
{
    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * @var string
     */
    private $pepper;

    /**
     * HaliteAdapter constructor.
     * @param $key
     * @throws InvalidKey
     * @throws \TypeError
     */
    public function __construct($key, $pepper)
    {
        if (Binary::safeStrlen($key) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {

            throw new InvalidKey(
                'Encryption key used for ' . __CLASS__ . '::' . __FUNCTION__ . ' must be exactly ' . \Sodium\CRYPTO_STREAM_KEYBYTES . ' characters long'
            );
        }

        if (Binary::safeStrlen($pepper) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {

            throw new InvalidKey(
                'Encryption pepper used for ' . __CLASS__ . '::' . __FUNCTION__ . ' must be exactly ' . \Sodium\CRYPTO_STREAM_KEYBYTES . ' characters long'
            );
        }

        $this->setKey((new EncryptionKey((new HiddenString($key)))));
        $this->setPepper($pepper);
    }

    /**
     * @param string $data
     * @return string
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function hash(string $data): string
    {
        return Password::hash(new HiddenString($data . $this->getPepper()), $this->getKey());
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
     * @return HaliteHashingAdapter
     */
    public function setKey(EncryptionKey $key): HaliteHashingAdapter
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
     * @return HaliteHashingAdapter
     */
    public function setPepper(string $pepper): HaliteHashingAdapter
    {
        $this->pepper = $pepper;
        return $this;
    }

}