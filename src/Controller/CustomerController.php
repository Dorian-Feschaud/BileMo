<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Service\CustomSerializerInterface;
use App\Service\CustomValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api/customers')]
final class CustomerController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CustomSerializerInterface $serializer,
        private readonly CustomValidatorInterface $validator,
        private readonly TagAwareCacheInterface $cache
    )
    {
    }

    /**
     * Permet de récupérer la liste des clients.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des clients',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Customer::class, groups: ['read:customer']))
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
    #[OA\Tag(name: 'Clients')]
    #[Route('', name: 'customers', methods: ['GET'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN"))'
        ),
    message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomers(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Customer::class, $request, null), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de récupérer les informations d'un seul client.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations d\'un client',
        content: new Model(type: Customer::class, groups: ['read:customer'])
    )]
    #[OA\Tag(name: 'Clients')]
    #[Route('/{id}', name: 'customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer().getId() === subject.getId())'
        ),
        subject: 'customer',
        message: 'Vous ne disposez pas des droits pour voir ces données'
    )]
    public function getCustomer(Customer $customer): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Customer::class, null, $customer), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de créer un nouveau client.
     */
    #[OA\Response(
        response: 201,
        description: 'Créer un nouveau client et le retourne',
        content: new Model(type: Customer::class, groups: ['read:customer'])
    )]
    #[OA\RequestBody(
        content: new Model(type: Customer::class, groups: ['create:customer'])
    )]
    #[OA\Tag(name: 'Clients')]
    #[Route('', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un client')]
    public function createCustomer(Request $request): JsonResponse
    {
        $customer = $this->serializer->deserialize(Customer::class, $request);

        $customer->setUsers(new ArrayCollection());
        $customer->setProducts(new ArrayCollection());

        $this->validator->validate($customer);

        $this->cache->invalidateTags(['customerCache']);
        
        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize(Customer::class, null, $customer), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    /**
     * Permet de modifier un client.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier un client',
    )]
    #[OA\RequestBody(
        content: new Model(type: Customer::class, groups: ['create:customer'])
    )]
    #[OA\Tag(name: 'Clients')]
    #[Route('/{id}', name: 'updateCustomer', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted(
        new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or ' .
            '(is_granted("ROLE_ADMIN") and user.getCustomer().getId() === subject.getId())'
        ),
        subject: 'customer',
        message: 'Vous ne disposez pas des droits pour modifier un client'
    )]
    public function updateCustomer(Customer $customer, Request $request): JsonResponse
    {
        $requestedCustomer = $this->serializer->deserialize(Customer::class, $request);

        $customer->setName($requestedCustomer->getName());

        $this->validator->validate($customer);

        $this->cache->invalidateTags(['customerCache']);

        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de supprimer un client.
     */
    #[OA\Response(
        response: 204,
        description: 'Supprimer un client',
    )]
    #[OA\Tag(name: 'Clients')]
    #[Route('/{id}', name: 'deleteCustomer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un client')]
    public function deleteCustomer(Customer $customer): JsonResponse
    {
        $this->cache->invalidateTags(['customerCache']);

        $this->em->remove($customer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
