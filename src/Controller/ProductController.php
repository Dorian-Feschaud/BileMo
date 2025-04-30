<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Product;
use App\Service\CleanGetterSetterInterface;
use App\Service\CustomSerializerInterface;
use App\Service\CustomValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api/products')]
final class ProductController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CustomSerializerInterface $serializer,
        private readonly CustomValidatorInterface $validator,
        private readonly TagAwareCacheInterface $cache,
        private readonly CleanGetterSetterInterface $cleanGetterSetter
    )
    {
    }
    
    /**
     * Permet de récupérer la liste des produits.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class, groups: ['read:product']))
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
    #[OA\Tag(name: 'Produits')]
    #[Route('', name: 'products', methods: ['GET'])]
    public function getProducts(Request $request): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Product::class, $request, null), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de récupérer les informations d'un seul produit.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations d\'un produit',
        content: new Model(type: Product::class, groups: ['read:product'])
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('/{id}', name: 'product', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize(Product::class, null, $product), Response::HTTP_OK, [], true);
    }

    /**
     * Permet de créer un nouveau produit.
     */
    #[OA\Response(
        response: 201,
        description: 'Créer un nouveau produit et le retourne',
        content: new Model(type: Product::class, groups: ['read:product'])
    )]
    #[OA\RequestBody(
        content: new Model(type: Product::class, groups: ['create:product'])
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour créer un produit')]
    public function createProduct(Request $request): JsonResponse
    {
        $product = $this->serializer->deserialize(Product::class, $request);
        // $product->setCustomers([]);

        $this->validator->validate($product);

        $this->cache->invalidateTags(['productCache']);

        $this->em->persist($product);
        $this->em->flush();


        return new JsonResponse($this->serializer->serialize(Product::class, null, $product), Response::HTTP_CREATED, ['Location' => $this->urlGenerator->generate('product', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)], true);
    }

    /**
     * Permet de modifier un produit.
     */
    #[OA\Response(
        response: 200,
        description: 'Modifier un produit',
    )]
    #[OA\RequestBody(
        content: new Model(type: Product::class, groups: ['create:product'])
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('/{id}', name: 'updateProduct', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour modifier un produit')]
    public function updateProduct(Product $product, Request $request): JsonResponse
    {
        $requestedProduct = $this->serializer->deserialize(Product::class, $request);

        $this->validator->validate($requestedProduct);

        foreach ($request->toArray() as $property => $value) {
            try {
                $product->{$this->cleanGetterSetter->getCleanSetter($property)}($requestedProduct->{$this->cleanGetterSetter->getCleanGetter($property)}());
            } catch (Error $e) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $this->serializer->serializeErrors(['property_path' => $property, 'message' => 'Property ' . $property . ' is not a valid property.']));
            }
        }

        $this->validator->validate($product);

        $this->cache->invalidateTags(['productCache']);

        $this->em->persist($product);
        $this->em->flush();


        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de supprimer un produit.
     */
    #[OA\Response(
        response: 204,
        description: 'Supprimer un produit',
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('/{id}', name: 'deleteProduct', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour supprimer un produit')]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->cache->invalidateTags(['productCache']);

        $this->em->remove($product);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Permet de lier un produit à un ou plusieurs clients.
     */
    #[OA\Response(
        response: 200,
        description: 'Lier un produit à un ou plusieurs client',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: '{"customers": [1, 2, 3]}'
        )
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('/{id}/customers', name: 'addCustomers', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour lier un produit à un ou plusieurs clients')]
    public function addCustomers(Product $product, Request $request): JsonResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findBy(['id' => $request->toArray()['customers'] ?? []]);
        foreach ($customers as $customer) {
            if (!$product->getCustomers()->contains($customer)) {
                $product->addCustomer($customer);
                $customer->addProduct($product);
            }
        }

        $this->validator->validate($product);

        $this->cache->invalidateTags(['productCache']);

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Permet de retirer un produit à un ou plusieurs clients.
     */
    #[OA\Response(
        response: 200,
        description: 'Retirer un produit à un ou plusieurs client',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            example: '{"customers": [1, 2, 3]}'
        )
    )]
    #[OA\Tag(name: 'Produits')]
    #[Route('/{id}/customers', name: 'removeCustomers', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous ne disposez pas des droits pour retirer un produit à un ou plusieurs clients')]
    public function removeCustomers(Product $product, Request $request): JsonResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findBy(['id' => $request->toArray()['customers'] ?? []]);
        foreach ($customers as $customer) {
            if ($product->getCustomers()->contains($customer)) {
                $product->removeCustomer($customer);
                $customer->removeProduct($product);
            }
        }

        $this->validator->validate($product);

        $this->cache->invalidateTags(['productCache']);

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
