<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
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

    #[Route('', name: 'customers', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomers(CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($customerRepository->findAll(), 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un client')]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository): JsonResponse
    {
        $content = $request->toArray();

        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        if (isset($content['idsUsers'])) {
            $users = [];
            foreach ($content['idsUsers'] as $idUser) {
                $users[] = $userRepository->find($idUser);
            }
            $customer->setUsers(new ArrayCollection($users));
        }
        
        $em->persist($customer);
        $em->flush();

        return new JsonResponse($serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['read:customer'])), Response::HTTP_CREATED, ['Location' => $urlGenerator->generate('customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateCustomer', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un client')]
    public function updateCustomer(Customer $customer, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $requestedCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $customer->setName($requestedCustomer->getName());
        $customer->setUsers($requestedCustomer->getUsers());
        $customer->setProducts($requestedCustomer->getProducts());

        $em->persist($customer);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'deleteCustomer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un client')]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($customer);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
