<?php

namespace App\Service;

use JMS\Serializer\SerializationContext;
use Symfony\Bundle\SecurityBundle\Security;

class SerializationContextGeneratorInterface {

    private $accessGranted;

    public function __construct(Security $security)
    {
          $this->accessGranted = $security->isGranted('ROLE_SUPER_ADMIN');
    }
    
    public function createContext(String $method,String $entityClass): SerializationContext
    {
        $groups = [$method . ':' . $entityClass];
        
        if ($this->accessGranted) {
            $groups[] = $method . ':' . $entityClass . ':' . 'superadmin';
        }

        return SerializationContext::create()->setGroups($groups);
    }
}