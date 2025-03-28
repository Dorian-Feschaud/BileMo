<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomSerializerInterface {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly SerializationContextGeneratorInterface $serializationContextGenerator
    )
    {
        
    }
    
    public function serialize(String $entityName, ?Request $request, ?Object $entity): String
    {
        if ($entity != null) {
            $dataToSerialize = $entity;
        }
        else {
            $dataToSerialize = $this->em->getRepository($entityName)->findByPageLimit($request->get('page', 1), $request->get('limit', 10));
        }

        return $this->serializer->serialize($dataToSerialize, 'json', $this->serializationContextGenerator->createContext('read', $entityName));
    }

    public function deserialize(String $entityName, Request $request): mixed
    {
        return $this->serializer->deserialize($request->getContent(), $entityName, 'json', $this->serializationContextGenerator->createContext('create', $entityName));
    }
}