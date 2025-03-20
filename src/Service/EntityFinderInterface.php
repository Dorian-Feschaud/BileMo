<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EntityFinderInterface {
    
    public function getEntities(EntityManagerInterface $em, String $entity, Request $request): array
    {
        return $em->getRepository($entity)->findByPageLimit($request->get('page', 1), $request->get('limit', 10));
    }
}