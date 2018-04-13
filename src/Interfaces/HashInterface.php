<?php

namespace ZfDoctrineEncryptModule\Interfaces;

/**
 * Interface for hashors
 */
interface HashInterface
{
    /**
     * Must accept string ready for hashing. Returns hash.
     *
     * @param string $data
     * @return string
     */
    public function hash(string $data): string;
}
