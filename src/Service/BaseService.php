<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class BaseService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ServiceEntityRepositoryInterface
     */
    protected $repository;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * BaseService constructor.
     * @param EntityManagerInterface $entityManager
     * @param null|EventDispatcherInterface $dispatcher
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?EventDispatcherInterface $dispatcher,
        ?LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->repository = $this->getRepository();
    }

    /**
     * @param null|string $entryClass
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entryClass = null)
    {
        if ($entryClass != null) {
            return $this->entityManager->getRepository($entryClass);
        }
        return $this->entityManager->getRepository($this->getEntityClass());
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function logError(string $message, array $context)
    {
        $this->logger && $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function logWarning(string $message, array $context)
    {
        $this->logger && $this->logger->warning($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function logInfo(string $message, array $context)
    {
        $this->logger && $this->logger->warning($message, $context);
    }

    /**
     * @param object $object
     */
    public function persist($object)
    {
        $this->entityManager->persist($object);
    }

    /**
     * void
     */
    protected function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @param object $object
     */
    protected function remove($object)
    {
        $this->entityManager->remove($object);
    }


    /**
     * @return string
     */
    abstract public function getEntityClass();
}
