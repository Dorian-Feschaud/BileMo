<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomValidatorInterface {

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CustomSerializerInterface $serializer
    )
    {
        
    }

    
    public function validate(Object $entity): void
    {
        $errors = $this->validator->validate($entity);

        if ($errors->count() > 0) {
            throw new CustomException(Response::HTTP_BAD_REQUEST, $this->serializer->serializeErrors($errors));
        }
    }
}