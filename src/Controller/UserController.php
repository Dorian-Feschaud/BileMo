<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Service\EntityFinderInterface;
use App\Service\SerializationContextGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
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

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SerializationContextGeneratorInterface $serializationContextGenerator,
        private readonly EntityFinderInterface $ef
    )
    {
    }

    #[Route('', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getUsers(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->ef->getEntities($this->em, User::class, $request), 'json', $this->serializationContextGenerator->createContext('read', 'user')), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'user', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getDetailsUser(User $user): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($user, 'json', $this->serializationContextGenerator->createContext('read', 'user')), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createUser', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un utilisateur')]
    public function createUser(Request $request): JsonResponse
    {
        $content = $request->toArray();

        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', DeserializationContext::create()->setGroups(['create:user']));

        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }

        if (isset($content['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $content['password']));
        }
        
        if (isset($content['idCustomer'])) {
            $user->setCustomer($this->em->getRepository(Customer::class)->find($content['idCustomer']));
        } 

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($user, 'json', $this->serializationContextGenerator->createContext('read', 'user')), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateUser', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un utilisateur')]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $content = $request->toArray();

        $requestedUser = $this->serializer->deserialize($request->getContent(), User::class, 'json', DeserializationContext::create()->setGroups(['create:user']));

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
