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

final class UserController extends AbstractController{

    #[Route('api/users', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();

        $groups = ['read:user'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonUsers = $serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('api/users/{id}', name: 'user', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getDetailsUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $groups = ['read:user'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonUser = $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('api/users', name: 'createUser', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $passwordHasher, CustomerRepository $customerRepository): JsonResponse
    {
        $groups = ['create:user'];

        $context = DeserializationContext::create()
                ->setGroups($groups);

        $user = $serializer->deserialize($request->getContent(), User::class, 'json', $context);

        $content = $request->toArray();

        $plainPassword = $content['password'] ?? -1;
        $password = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($password);

        $roles = $content['roles'] ?? -1;
        $user->setRoles($roles);

        $idCustomer = $content['idCustomer'] ?? -1;
        $customer = $customerRepository->find($idCustomer);
        $user->setCustomer($customer);

        $em->persist($user);
        $em->flush();

        $groups = ['read:user'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('customer', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('api/users/{id}', name: 'updateUser', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un utilisateur')]
    public function updateUser(User $user, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CustomerRepository $customerRepository): JsonResponse
    {
        $groups = ['create:user'];

        $context = DeserializationContext::create()
                ->setGroups($groups);

        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json', $context);

        $content = $request->toArray();

        $plainPassword = $content['password'] ?? -1;
        $password = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($password);

        $roles = $content['roles'] ?? -1;
        $user->setRoles($roles);

        $idCustomer = $content['idCustomer'] ?? -1;
        $customer = $customerRepository->find($idCustomer);
        $user->setCustomer($customer);

        $user->setEmail($newUser->getEmail());
        $user->setFirstname($newUser->getFirstname());
        $user->setLastname($newUser->getLastname());

        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
