<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Service\CustomSerializerInterface;
use App\Service\CustomValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/users')]
final class UserController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CustomSerializerInterface $serializer,
        private readonly CustomValidatorInterface $validator
    )
    {
    }

    #[Route('', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getUsers(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(User::class, $request, null), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'user', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getDetailsUser(User $user): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(User::class, null, $user), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createUser', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un utilisateur')]
    public function createUser(Request $request): JsonResponse
    {
        $content = $request->toArray();

        $user = $this->serializer->deserialize(User::class, $request);

        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }

        if (isset($content['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $content['password']));
        }
        
        if (isset($content['idCustomer'])) {
            $user->setCustomer($this->em->getRepository(Customer::class)->find($content['idCustomer']));
        }

        $this->validator->validate($user);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize(User::class, null, $user), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateUser', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un utilisateur')]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $content = $request->toArray();

        $requestedUser = $this->serializer->deserialize(User::class, $request);

        $user->setEmail($requestedUser->getEmail());
        $user->setFirstname($requestedUser->getFirstname());
        $user->setLastname($requestedUser->getLastname());

        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }

        if (isset($content['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $content['password']));
        }
        
        if (isset($content['idCustomer'])) {
            $user->setCustomer($this->em->getRepository(Customer::class)->find($content['idCustomer']));
        }

        $this->validator->validate($user);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('{id}', name: 'deleteUser', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un utilisateur')]
    public function deleteUser(User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
