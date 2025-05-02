<?php

namespace App\Service;

class CleanGetterSetterInterface {
    
    public function getCleanGetter(String $property): String
    {
        return 'get' . $this->getCleanProperty($property);
    }

    public function getCleanSetter(String $property): String
    {
        return 'set' . $this->getCleanProperty($property);
    }

    private function getCleanProperty(String $property): String
    {
        $words = explode('_', $property);
        $res = '';
        foreach ($words as $word) {
            $res .= ucfirst($word);
        }

        return $res;
    }
}