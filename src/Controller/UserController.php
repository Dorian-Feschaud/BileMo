<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
}
