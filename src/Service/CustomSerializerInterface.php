<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CustomSerializerInterface {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly SerializationContextGeneratorInterface $serializationContextGenerator,
        private readonly TagAwareCacheInterface $cache
    )
    {
        
    }
    
    public function serialize(String $entityName, ?Request $request, ?Object $entity): String
    {
        $cleanEntityName = $this->getCleanEntityName($entityName);

        $cacheName = 'get' . ucfirst($cleanEntityName);

        if ($entity != null) {
            $dataToSerialize = $entity;
            $cacheName .= '-' . $entity->getId();
        }
        else {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $dataToSerialize = $this->em->getRepository($entityName)->findByPageLimit($page, $limit);
            $cacheName .= 's-' . $page . '-' . $limit;
        }

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($dataToSerialize, $entityName, $cleanEntityName) {
            $item->tag($cleanEntityName . 'Cache');
            return $this->serializer->serialize($dataToSerialize, 'json', $this->serializationContextGenerator->createContext('read', $this->getCleanEntityName($entityName)));
        });
    }

    public function deserialize(String $entityName, Request $request): mixed
    {
        return $this->serializer->deserialize($request->getContent(), $entityName, 'json', $this->serializationContextGenerator->createContext('create', $this->getCleanEntityName($entityName)));
    }

    public function serializeErrors(ConstraintViolationListInterface $errors) {
        return $this->serializer->serialize($errors, 'json');
    }

    protected function getCleanEntityName(String $entityClass): String
    {
        $tmp = explode('\\', $entityClass);

        return strtolower(end($tmp));
    }
}