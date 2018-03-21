<?php

namespace ZfDoctrineEncryptModule\Interfaces;

interface PepperInterface
{
    /**
     * @return string
     */
    public function getPepper(): string;

    /**
     * @param string $pepper
     * @return PepperInterface
     */
    public function setPepper(string $pepper): PepperInterface;
}