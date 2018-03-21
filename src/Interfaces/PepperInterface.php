<?php

namespace ZfDoctrineEncryptModule\Interfaces;

interface PepperInterface
{
    /**
     * @return string
     */
    public function getPepper(): string;

    /**
     * @param string $salt
     * @return PepperInterface
     */
    public function setPepper(string $salt): PepperInterface;
}