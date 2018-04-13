<?php

namespace ZfDoctrineEncryptModule\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfDoctrineEncryptModule\Adapter\HaliteHashingAdapter;
use ZfDoctrineEncryptModule\Interfaces\HashInterface;
use ZfDoctrineEncryptModule\Options\HashModuleOptions;
use ZfDoctrineEncryptModule\Subscriber\DoctrineHashedSubscriber;

class ZfDoctrineHashedServiceFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface $container
     * @return DoctrineHashedSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        /** @var HashModuleOptions $options */
        $options = $this->getOptions($container, 'hashing');

        /** @var Reader|AnnotationReader $reader */
        $reader = $this->createReader($container, $options->getReader());
        /** @var HashInterface|HaliteHashingAdapter $adapter */
        $adapter = $this->createAdapter(
            $container,
            $options->getAdapter(),
            [
                'key' => $options->getKey(),
                'pepper' => $options->getPepper(),
            ]
        );

        return new DoctrineHashedSubscriber(
            $reader,
            $adapter
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return DoctrineHashedSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createService($container);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return HashModuleOptions::class;
    }

    /**
     * @param ContainerInterface $container
     * @param string $reader
     * @param array|null $options
     * @return Reader
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createReader(ContainerInterface $container, string $reader, array $options = null)
    {
        /** @var Reader $reader */
        $reader = $this->hydrateDefinition($reader, $container, $options);

        if (!$reader instanceof Reader) {

            throw new \InvalidArgumentException(
                'Invalid reader provided. Must implement ' . Reader::class
            );
        }

        return $reader;
    }

    /**
     * @param ContainerInterface $container
     * @param $adapter
     * @param array|null $options
     * @return HashInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createAdapter(ContainerInterface $container, string $adapter, array $options = null)
    {
        /** @var HashInterface $adapter */
        $adapter = $this->hydrateDefinition($adapter, $container, $options);

        if (!$adapter instanceof HashInterface) {
            throw new \InvalidArgumentException(
                'Invalid hashor provided, must be a service name, '
                . 'class name, an instance, or method returning an ' . HashInterface::class
            );
        }

        return $adapter;
    }

    /**
     * Hydrates the value into an object
     *
     * @param $value
     * @param ContainerInterface $container
     * @param array|null $options
     * @return mixed
     */
    private function hydrateDefinition($value, ContainerInterface $container, array $options = null)
    {
        if (is_string($value)) {
            if ($container->has($value)) {
                if (is_array($options)) {
                    $value = $container->build($value, $options);
                } else {
                    $value = $container->get($value);
                }
            } elseif (class_exists($value)) {
                $value = new $value();
            }
        } elseif (is_callable($value)) {
            $value = $value();
        }

        return $value;
    }
}