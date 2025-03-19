<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\SerializationContextGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/products')]
final class ProductController extends AbstractController{

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SerializationContextGeneratorInterface $serializationContextGenerator
    )
    {
    }

    #[Route('', name: 'products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->em->getRepository(Product::class)->findAll(), 'json', $this->serializationContextGenerator->createContext('read', 'product')), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'product', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($product, 'json', $this->serializationContextGenerator->createContext('read', 'product')), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un produit')]
    public function createProduct(Request $request): JsonResponse
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($product, 'json', $this->serializationContextGenerator->createContext('read', 'product')), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('product', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateProduct', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un produit')]
    public function updateProduct(Product $product, Request $request): JsonResponse
    {
        $requestedProduct = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

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

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'deleteProduct', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un produit')]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
