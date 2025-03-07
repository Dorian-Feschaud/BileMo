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

    #[Route('api/customers', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un client')]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $content = $request->toArray();

        $idAdmin = $content['idAdmin'] ?? -1;
        $admin = $userRepository->find($idAdmin);
        $customer->setAdmin($admin);

        $idsUsers = $content['idsUsers' ?? -1];
        $users = [];
        foreach ($idsUsers as $idUser) {
            $user = $userRepository->find($idUser);
            $users[] = $user;
        }
        $customer->setUsers(new ArrayCollection($users));

        $em->persist($customer);
        $em->flush();

        $groups = ['getCustomers'];

        $context = SerializationContext::create()
                ->setGroups($groups);

        $jsonCustomer = $serializer->serialize($customer, 'json', $context);

        $location = $urlGenerator->generate('customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }
}
