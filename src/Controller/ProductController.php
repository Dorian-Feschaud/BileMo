<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $products = $productRepository->findAll();

        $jsonProducts = $serializer->serialize($products, 'json', null);

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('api/products/{id}', name: 'product', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json', null);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('api/products', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un produit')]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $em->persist($product);
        $em->flush();

        $jsonProduct = $serializer->serialize($product, 'json', null);

        $location = $urlGenerator->generate('product', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('api/products/{id}', name: 'updateProduct', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un produit')]
    public function updateBook(Product $product, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $newProduct = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $product->setName($newProduct->getName());
        $product->setManufacturer($newProduct->getManufacturer());
        $product->setReleaseDate($newProduct->getReleaseDate());
        $product->setPrice($newProduct->getPrice());
        $product->setColor($newProduct->getColor());
        $product->setCapacity($newProduct->getCapacity());
        $product->setWidth($newProduct->getWidth());
        $product->setHeight($newProduct->getHeight());
        $product->setThickness($newProduct->getThickness());
        $product->setWeight($newProduct->getWeight());
        $product->setScreen($newProduct->getScreen());
        $product->setScreenHeight($newProduct->getScreenHeight());
        $product->setScreenWidth($newProduct->getScreenWidth());
        $product->setScreenResolution($newProduct->getScreenResolution());
        $product->setBackCamera($newProduct->getBackCamera());
        $product->setBackCameraResolution($newProduct->getBackCameraResolution());
        $product->setFrontCameraResolution($newProduct->getFrontCameraResolution());
        $product->setProcessor($newProduct->getProcessor());
        $product->setRam($newProduct->getRam());
        $product->setBatteryCapacity($newProduct->getBatteryCapacity());
        $product->setNetwork($newProduct->getNetwork());

        $em->persist($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('api/products/{id}', name: 'deleteProduct', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un produit')]
    public function deleteBook(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
