<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Configuration;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        // Création d'un user "normal"
        $client = new Client();
        $client->setEmail("client@bilemoapi.com");
        $client->setFirstname("John");
        $client->setLastname("Doe");
        $client->setRoles(["ROLE_USER"]);
        $client->setPassword($this->userPasswordHasher->hashPassword( $client, "password"));
        $manager->persist( $client);
        
        // Création d'un user admin
        $clientAdmin = new Client();
        $clientAdmin->setEmail("admin@bilemoapi.com");
        $clientAdmin->setFirstname("Daddy");
        $clientAdmin->setLastname("Daddy");
        $clientAdmin->setRoles(["ROLE_ADMIN"]);
        $clientAdmin->setPassword($this->userPasswordHasher->hashPassword($clientAdmin, "password"));
        $manager->persist($clientAdmin);

        

        $manager->flush();
        
    
    //     for($i = 0; $i < 20 ; $i++){
    //         $image = new Image();
    //         $configuration = new Configuration();
    //         $product = new Product();
    //         $product->setName("mobile ". $i);
    //         $product->setScreen(0 .".".$i);
    //         $product->setHeigth(0 .".".$i);
    //         $product->setWeight(0 .".".$i);
    //         $product->setWidth(0 .".".$i);
    //         $product->setLength( 0 .".".$i);
    //         $product->setVideo("video ".$i);
    //         $product->setWifi( true);
    //         $product->setCamera(true);
    //         $product->setBluetooth(true);
    //         $product->setDescription("Description ".$i);

            
    //         $configuration->setMemory("10.$i");
    //         $configuration->setPrice(100 .".". $i);
    //         $configuration->setColor("rouge noire $i");
    //         $configuration->setProduct($product);
            
    //          $image->setUrl("https://monImage $i");
    //          $image->setProduct($product);
    //         $manager->persist($configuration);
    //          $manager->persist($image);
    //          $manager->persist($product);
    //     }
       
        

    //    $manager->flush();
    // 
    }

    
}
