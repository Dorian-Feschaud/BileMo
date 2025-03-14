<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
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

final class ProductController extends AbstractController{

    #[Route('api/products', name: 'products', methods: ['GET'])]
    public function getProducts(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($productRepository->findAll(), 'json', SerializationContext::create()->setGroups($this->isGranted('ROLE_SUPER_ADMIN') ? ['read:product', 'read:product:superadmin'] : ['read:product'])), Response::HTTP_OK, [], true);
    }

    #[Route('api/products/{id}', name: 'product', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($product, 'json', SerializationContext::create()->setGroups($this->isGranted('ROLE_SUPER_ADMIN') ? ['read:product', 'read:product:superadmin'] : ['read:product'])), Response::HTTP_OK, [], true);
    }

    #[Route('api/products', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un produit')]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $em->persist($product);
        $em->flush();

        return new JsonResponse($serializer->serialize($product, 'json', SerializationContext::create()->setGroups($this->isGranted('ROLE_SUPER_ADMIN') ? ['read:product', 'read:product:superadmin'] : ['read:product'])), Response::HTTP_CREATED, ['Location' => $urlGenerator->generate('product', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('api/products/{id}', name: 'updateProduct', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un produit')]
    public function updateProduct(Product $product, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $requestedProduct = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $product->setName($requestedProduct->getName());
        $product->setManufacturer($requestedProduct->getManufacturer());
        $product->setReleaseDate($requestedProduct->getReleaseDate());
        $product->setPrice($requestedProduct->getPrice());
        $product->setColor($requestedProduct->getColor());
        $product->setCapacity($requestedProduct->getCapacity());
        $product->setWidth($requestedProduct->getWidth());
        $product->setHeight($requestedProduct->getHeight());
        $product->setThickness($requestedProduct->getThickness());
        $product->setWeight($requestedProduct->getWeight());
        $product->setScreen($requestedProduct->getScreen());
        $product->setScreenHeight($requestedProduct->getScreenHeight());
        $product->setScreenWidth($requestedProduct->getScreenWidth());
        $product->setScreenResolution($requestedProduct->getScreenResolution());
        $product->setBackCamera($requestedProduct->getBackCamera());
        $product->setBackCameraResolution($requestedProduct->getBackCameraResolution());
        $product->setFrontCameraResolution($requestedProduct->getFrontCameraResolution());
        $product->setProcessor($requestedProduct->getProcessor());
        $product->setRam($requestedProduct->getRam());
        $product->setBatteryCapacity($requestedProduct->getBatteryCapacity());
        $product->setNetwork($requestedProduct->getNetwork());

        $em->persist($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('api/products/{id}', name: 'deleteProduct', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un produit')]
    public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
