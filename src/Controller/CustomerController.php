<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CustomerController extends AbstractController{

    #[Route('api/customers', name: 'customers', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomers(CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $customerRepository->findAll();

        $groups = ['getCustomers'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonProducts = $serializer->serialize($customers, 'json', $context);

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('api/customers/{id}', name: 'customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour voir ces données')]
    public function getCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
        $groups = ['getCustomers'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonCustomer = $serializer->serialize($customer, 'json', $context);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }
}
