<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\User;
use App\Service\SerializationContextGeneratorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/customers')]
final class CustomerController extends AbstractController{

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SerializationContextGeneratorInterface $serializationContextGenerator
    )
    {
    }

    #[Route('', name: 'customers', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomers(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->em->getRepository(Customer::class)->findByPageLimit($request->get('page', 1), $request->get('limit', 10)), 'json', $this->serializationContextGenerator->createContext('read', 'customer')), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomer(Customer $customer): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($customer, 'json', $this->serializationContextGenerator->createContext('read', 'customer')), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un client')]
    public function createCustomer(Request $request): JsonResponse
    {
        $customer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json', DeserializationContext::create()->setGroups(['create:customer']));

        $customer->setUsers(new ArrayCollection());
        $customer->setProducts(new ArrayCollection());
        
        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($customer, 'json', $this->serializationContextGenerator->createContext('read', 'customer')), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateCustomer', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un client')]
    public function updateCustomer(Customer $customer, Request $request): JsonResponse
    {
        $requestedCustomer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json', DeserializationContext::create()->setGroups(['create:customer']));

        $customer->setName($requestedCustomer->getName());

        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'deleteCustomer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un client')]
    public function deleteCustomer(Customer $customer): JsonResponse
    {
        $this->em->remove($customer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
