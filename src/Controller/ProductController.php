<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Entity\Configuration;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'app_product',methods:'GET')]
    public function getAllProducts( Request $request,ProductRepository $productRepo,
    SerializerInterface $serializer,TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $jsonProductList = $cache->get($idCache, function (ItemInterface $item) use ($productRepo, $page, $limit, $serializer) {
            $item->tag("productsCache");
            $ProductList = $productRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($ProductList, 'json', ['groups' => 'getProduct']);
        });
      
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'app_product_detail',methods:'GET')]
    public function getDetailProduct(Product $product,ProductRepository $productRepo,SerializerInterface $serializer):JsonResponse
    {
        $jsonProduct = $serializer->serialize($product,'json',['groups' => 'getProduct']);
        return new JsonResponse($jsonProduct,Response::HTTP_OK,['accept'=>'json'],true);
    }

   

    #[Route('/api/products/{id}', name: 'app_delete_product',methods:'DELETE')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer ce produit')]
    public function deleteProduct( Product $product, EntityManagerInterface $em, TagAwareCacheInterface $cachePool):JsonResponse
    {
        $cachePool->invalidateTags(["productsCache"]);
        $em->remove($product);
        $em->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/products', name: 'app_create_product',methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un produit')]
    public function createProduct(Request $request, SerializerInterface $serializer, 
    EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,ValidatorInterface $validator)
    {
       $product = $serializer->deserialize($request->getContent(),Product::class,'json');

        // On vérifie les erreurs
        $errors = $validator->validate($product);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $product->setClient($this->getUser());
        $em->persist($product);
        $em->flush();

        //On Renvoi le produit créé par l'utilisateur en json
        $jsonProduct = $serializer->serialize($product,'json',['groups'=>'getProduct']);
        $location = $urlGenerator->generate('app_product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse( $jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);



    }
    #[Route('/api/products/image', name: 'app_add_image',methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter une image à un produit')]
    public function addImage(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, 
    UrlGeneratorInterface $urlGenerator, ProductRepository $productRepo)
    {
        $image = $serializer->deserialize($request->getContent(),Image::class,'json');

         // Récupération de l'ensemble des données envoyées sous forme de tableau
         $content = $request->toArray();

         // Récupération de l'idProduct. S'il n'est pas défini, alors on met -1 par défaut.
         $idProduct = $content['idProduct'] ?? -1;
 
         // On cherche le produit qui correspond et on l'assigne à l'image.
         // Si "find" ne trouve pas le produit, alors null sera retourné.
         $image->setProduct($productRepo->find($idProduct));
         
         $em->persist($image);
         $em->flush();
         $jsonImage = $serializer->serialize($image, 'json', ['groups' => 'getProduct']);
 
         $location = $urlGenerator->generate('app_product_detail', ['id' =>$idProduct], UrlGeneratorInterface::ABSOLUTE_URL);
 
         return new JsonResponse( $jsonImage, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/products/configuration', name: 'app_add_configuration',methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter une configuration à un produit')]
    public function addConfiguration(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
     UrlGeneratorInterface $urlGenerator, ProductRepository $productRepo)
    {
       
        $configuration = $serializer->deserialize($request->getContent(),Configuration::class,'json');

         // Récupération de l'ensemble des données envoyées sous forme de tableau
         $content = $request->toArray();

         // Récupération de l'idProduct. S'il n'est pas défini, alors on met -1 par défaut.
         $idProduct = $content['idProduct'] ?? -1;
 
         // On cherche le produit qui correspond et on l'assigne à la configuration.
         // Si "find" ne trouve pas le produit, alors null sera retourné.
         $configuration->setProduct($productRepo->find( $idProduct));
         
         $em->persist($configuration);
         $em->flush();
         $jsonImage = $serializer->serialize($configuration, 'json', ['groups' => 'getProduct']);
 
         $location = $urlGenerator->generate('app_product_detail', ['id' =>$productRepo->find( $idProduct)->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
 
         return new JsonResponse( $jsonImage, Response::HTTP_CREATED, ["Location" => $location], true);

    }

    
    #[Route('/api/products/{id}', name: 'app_update_product',methods:'PUT')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un produit')]
    public function updateProduct(Request $request, SerializerInterface $serializer, Product $currentProduct, EntityManagerInterface $em, ProductRepository $productRepo)
    {

        $updatedProduct = $serializer->deserialize($request->getContent(), 
        Product::class, 
        'json', 
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}   
