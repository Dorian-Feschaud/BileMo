<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Service\CustomSerializerInterface;
use App\Service\CustomValidatorInterface;
use App\Service\UserTokenInterface;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api/users')]
final class UserController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CustomSerializerInterface $serializer,
        private readonly CustomValidatorInterface $validator,
        private readonly TagAwareCacheInterface $cache,
        private readonly UserTokenInterface $userToken
    )
    {
    }

    /**
     * Permet de récupérer la liste des utilisateurs.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['read:user']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'La page que l\'on veut récupérer',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre d\'éléments que l\'on veut récupérer',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('', name: 'users', methods: ['GET'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            'is_granted("ROLE_ADMIN")'
        ),
        message: 'Vous ne disposez pas des droits pour voir ces données'
    )]
    public function getUsers(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(User::class, $request, null), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de récupérer les informations d'un seul utilisateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations d\'un utilisateur',
        content: new Model(type: User::class, groups: ['read:user'])
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}', name: 'user', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer() === subject.getCustomer()) or ' .
            'user === subject'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour voir ces données'
    )]
    public function getDetailsUser(User $user): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(User::class, null, $user), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de créer un nouvel utilisateur.
     */
    #[OA\Response(
        response: 201,
        description: 'Créer un nouvel utilisateur et le retourne',
        content: new Model(type: User::class, groups: ['read:user'])
    )]
    #[OA\RequestBody(
        content: new Model(type: User::class, groups: ['create:user'])
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('', name: 'createUser', methods: ['POST'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            'is_granted("ROLE_ADMIN")'
        ),
        message: 'Vous ne disposez pas des droits pour créer un utilisateur')]
    public function createUser(Request $request): JsonResponse
    {
        $content = $request->toArray();

        $user = $this->serializer->deserialize(User::class, $request);

        if (isset($content['roles'])) {
            $user->setRoles($content['roles']);
        }
        else {
            $user->setRoles(['ROLE_MEMBER']);
        }

        if (isset($content['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $content['password']));
        }

        $currentUser = $this->userToken->getCurrentUser();

        if ($currentUser instanceof User) {
            $user->setCustomer($currentUser->getCustomer());
        }
        
        $this->validator->validate($user);

        $this->cache->invalidateTags(['userCache']);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize(User::class, null, $user), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    /**
     * Permet de modifier un utilisateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier un utilisateur',
    )]
    #[OA\RequestBody(
        content: new Model(type: User::class, groups: ['create:user'])
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}', name: 'updateUser', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer() === subject.getCustomer()) or ' .
            'user === subject'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour modifier un utilisateur'
    )]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $requestedUser = $this->serializer->deserialize(User::class, $request);

        $user->setEmail($requestedUser->getEmail());
        $user->setFirstname($requestedUser->getFirstname());
        $user->setLastname($requestedUser->getLastname());

        $this->validator->validate($user);

        $this->cache->invalidateTags(['userCache']);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de supprimer un utilisateur.
     */
    #[OA\Response(
        response: 204,
        description: 'Supprimer un utilisateur',
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}', name: 'deleteUser', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer() === subject.getCustomer())'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour supprimer un utilisateur'
    )]
    public function deleteUser(User $user): JsonResponse
    {
        $this->cache->invalidateTags(['userCache']);

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Permet de modifier le mot de passe d'un utilisateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier le mot de passe d\'un utilisateur',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: '{"password": "string"}'
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}/resetPassword', name: 'resetPassword', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer() === subject.getCustomer()) or ' .
            'user === subject'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour modifier le mot de passe d\'un utilisateur'
    )]
    public function resetPassword(User $user, Request $request): JsonResponse
    {
        $password = $request->toArray()['password'] ?? '';
        $hashPassword = '';
        if ($password != null) {
            $hashPassword = $this->passwordHasher->hashPassword($user, $password);
        }
        $user->setPassword($hashPassword);

        $this->validator->validate($user);

        $this->cache->invalidateTags(['userCache']);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de modifier le role d'un utilisateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier le role d\'un utilisateur',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: '{"roles": ["string"]}'
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}/updateRoles', name: 'updateRoles', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN")'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour modifier le role d\'un utilisateur'
    )]
    public function updateRoles(User $user, Request $request): JsonResponse
    {
        $user->setRoles($request->toArray()['roles'] ?? []);

        $this->validator->validate($user);

        $this->cache->invalidateTags(['userCache']);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de modifier le client d'un utilisateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier le client d\'un utilisateur',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: '{"customer": 0}'
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    #[Route('/{id}/updateAdminCustomer', name: 'updateAdminCustomer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN")'
        ),
        subject: 'user',
        message: 'Vous ne disposez pas des droits pour modifier le client d\'un utilisateur'
    )]
    public function updateAdminCustomer(User $user, Request $request): JsonResponse
    {
        $user->setCustomer($this->em->getRepository(Customer::class)->find($request->toArray()['customer'] ?? -1));

        $this->validator->validate($user);

        $this->cache->invalidateTags(['userCache']);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
