<?php

namespace App\Service;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\SecurityBundle\Security;

class SerializationContextGeneratorInterface {

    private bool $accessGranted;

    public function __construct(Security $security)
    {
          $this->accessGranted = $security->isGranted('ROLE_SUPER_ADMIN');
    }
    
    public function createContext(String $method, String $entityName): mixed
    {
        $groups = [$method . ':' . strtolower($entityName)];
        
        if ($this->accessGranted) {
            $groups[] = $method . ':' . $entityName . ':' . 'superadmin';
        }

        switch ($method) {
            case 'read':
                return SerializationContext::create()->setGroups($groups);
            case 'create':
                return DeserializationContext::create()->setGroups($groups);
            default :
                return null;
        }
    }
}