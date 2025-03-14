<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
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

    #[Route('', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($userRepository->findAll(), 'json', SerializationContext::create()->setGroups(['read:user'])), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'user', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getDetailsUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['read:user'])), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createUser', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $passwordHasher, CustomerRepository $customerRepository): JsonResponse
    {
        $content = $request->toArray();

        $user = $serializer->deserialize($request->getContent(), User::class, 'json', DeserializationContext::create()->setGroups(['create:user']));
        
        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }

        if (isset($content['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $content['password']));
        }
        
        if (isset($content['idCustomer'])) {
            $user->setCustomer($customerRepository->find($content['idCustomer']));
        } 

        $em->persist($user);
        $em->flush();

        return new JsonResponse($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['read:user'])), Response::HTTP_CREATED, ['Location' => $urlGenerator->generate('customer', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateUser', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un utilisateur')]
    public function updateUser(User $user, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CustomerRepository $customerRepository): JsonResponse
    {
        $content = $request->toArray();

        $requestedUser = $serializer->deserialize($request->getContent(), User::class, 'json', DeserializationContext::create()->setGroups(['create:user']));

        $user->setEmail($requestedUser->getEmail());
        $user->setFirstname($requestedUser->getFirstname());
        $user->setLastname($requestedUser->getLastname());

        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }

        if (isset($content['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $content['password']));
        }
        
        if (isset($content['idCustomer'])) {
            $user->setCustomer($customerRepository->find($content['idCustomer']));
        }        

        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('{id}', name: 'deleteUser', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un utilisateur')]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
