<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
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

    private readonly SerializerInterface $serializer;
    private readonly EntityManagerInterface $em;
    private readonly UrlGeneratorInterface $urlGenerator;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('', name: 'customers', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomers(): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->em->getRepository(Customer::class)->findAll(), 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomer(Customer $customer): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un client')]
    public function createCustomer(Request $request): JsonResponse
    {
        $content = $request->toArray();

        $customer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json');

        if (isset($content['idsUsers'])) {
            $users = [];
            foreach ($content['idsUsers'] as $idUser) {
                $users[] = $this->em->getRepository(User::class)->find($idUser);
            }
            $customer->setUsers(new ArrayCollection($users));
        }
        
        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateCustomer', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un client')]
    public function updateCustomer(Customer $customer, Request $request): JsonResponse
    {
        $requestedCustomer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json');

        $customer->setName($requestedCustomer->getName());
        $customer->setUsers($requestedCustomer->getUsers());
        $customer->setProducts($requestedCustomer->getProducts());

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
