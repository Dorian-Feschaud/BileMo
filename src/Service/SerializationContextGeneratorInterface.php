<?php

namespace App\Service;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\SecurityBundle\Security;

class SerializationContextGeneratorInterface {

    private $accessGranted;

    public function __construct(Security $security)
    {
          $this->accessGranted = $security->isGranted('ROLE_SUPER_ADMIN');
    }
    
    public function createContext(String $method, String $entityClass): mixed
    {
        $entityName = $this->getCleanEntityName($entityClass);

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

    protected function getCleanEntityName(String $entityClass): String
    {
        $tmp = explode('\\', $entityClass);

        return strtolower(end($tmp));
    }
}