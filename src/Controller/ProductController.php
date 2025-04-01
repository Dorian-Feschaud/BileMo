<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CustomSerializerInterface;
use App\Service\CustomValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CustomSerializerInterface $serializer,
        private readonly CustomValidatorInterface $validator
    )
    {
    }

    #[Route('', name: 'products', methods: ['GET'])]
    public function getProducts(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Product::class, $request, null), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'product', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Product::class, null, $product), Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un produit')]
    public function createProduct(Request $request): JsonResponse
    {
        $product = $this->serializer->deserialize(Product::class, $request);

        $this->validator->validate($product);

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize(Product::class, null, $product), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('product', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    #[Route('/{id}', name: 'updateProduct', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un produit')]
    public function updateProduct(Product $product, Request $request): JsonResponse
    {
        $requestedProduct = $this->serializer->deserialize(Product::class, $request);

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

        $this->validator->validate($product);

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
