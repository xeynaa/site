<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Country;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Cart;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        // 1; créer un pays
        $france = new Country();
        $france->setName('France');
        $france->setCode('FR');
        $manager->persist($france);

        $espagne = new Country();
        $espagne->setName('Espagne');
        $espagne->setCode('ES');
        $manager->persist($espagne);

        $Madagascar = new Country();
        $Madagascar->setName('Madagascar');
        $Madagascar->setCode('MD');
        $manager->persist($Madagascar);

        $italie = new Country();
        $italie->setName('Italie');
        $italie->setCode('IT');
        $manager->persist($italie);


        // 2.créer un utilisateur super admin
        $sadmin = new User();
        $sadmin->setUsername('sadmin');
        $sadmin->setFirstName('Super');
        $sadmin->setLastName('Admin');
        $sadmin->setBirthDate(new \DateTime('1990-01-01'));
        $sadmin->setIsAdmin(true);
        $sadmin->setIsSuperAdmin(true);
        $sadmin->setCountry($france);
        $sadmin->setRoles(['ROLE_SUPER_ADMIN']);

        $gilles = new User();
        $gilles->setUsername('gilles');
        $gilles->setFirstName('Subrenat');
        $gilles->setLastName('Gilles');
        $gilles->setBirthDate(new \DateTime('1990-01-02'));
        $gilles->setIsAdmin(true);
        $gilles->setIsSuperAdmin(false);
        $gilles->setCountry($espagne);
        $gilles->setRoles(['ROLE_ADMIN']);

        $rita = new User();
        $rita->setUsername('rita');
        $rita->setFirstName('Zrour');
        $rita->setLastName('Rita');
        $rita->setBirthDate(new \DateTime('2000-01-01'));
        $rita->setIsAdmin(false);
        $rita->setIsSuperAdmin(false);
        $rita->setCountry($Madagascar);
        $rita->setRoles(['ROLE_CLIENT']);

        $boumediene = new User();
        $boumediene->setUsername('boumediene');
        $boumediene->setFirstName('Bou');
        $boumediene->setLastName('Bou');
        $boumediene->setBirthDate(new \DateTime('1990-01-05'));
        $boumediene->setIsAdmin(false);
        $boumediene->setIsSuperAdmin(false);
        $boumediene->setCountry($italie);
        $boumediene->setRoles(['ROLE_CLIENT']);



        //Mot de passe hashé
        $hashedPassword1 = $this->passwordHasher->hashPassword($sadmin, 'nimdas');
        $sadmin->setPassword($hashedPassword1);

        $hashedPassword2 = $this->passwordHasher->hashPassword($gilles, 'sellig');
        $gilles->setPassword($hashedPassword2);

        $hashedPassword3 = $this->passwordHasher->hashPassword($rita, 'atir');
        $rita->setPassword($hashedPassword3);

        $hashedPassword4 = $this->passwordHasher->hashPassword($boumediene, 'nimdas');
        $boumediene->setPassword($hashedPassword4);


        $manager->persist($sadmin);
        $manager->persist($gilles);
        $manager->persist($rita);
        $manager->persist($boumediene);

        //créer et inserer un produit

        $productsadmin = new Product();
        $productsadmin->setLabel('Stylo bleu');
        $productsadmin->setPrice(2.50);
        $productsadmin->setStock(100);
        $productsadmin->addCountry($france);

        $productgilles = new Product();
        $productgilles->setLabel('gomme');
        $productgilles->setPrice(1.50);
        $productgilles->setStock(10);
        $productgilles->addCountry($espagne);

        $productrita = new Product();
        $productrita->setLabel('PC');
        $productrita->setPrice(800.50);
        $productrita->setStock(3);
        $productrita->addCountry($france);

        $productboumediene = new Product();
        $productboumediene->setLabel('cahier');
        $productboumediene->setPrice(1.50);
        $productboumediene->setStock(1);
        $productboumediene->addCountry($france);


        $manager->persist($productsadmin);
        $manager->persist($productgilles);
        $manager->persist($productrita);
        $manager->persist($productboumediene);


        $manager->flush();
    }
}
