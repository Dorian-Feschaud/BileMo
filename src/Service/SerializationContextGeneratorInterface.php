<?php

namespace App\Service;

use JMS\Serializer\SerializationContext;

class SerializationContextGeneratorInterface {
    
    public function createContext(String $entityClass, String $method): SerializationContext
    {
        return SerializationContext::create()->setGroups([$entityClass . ':' . $method]);
    }
}