<?php

namespace App\Controller;

use App\Entity\Salarie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class InitController extends AbstractController
{
    #[Route('/init', name: 'app_init')]
    public function index(EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher, GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        $user = new User();
        $user->setEmail('etienne.buffet@gmail.com');
        $plaintextPassword = 'azerty123';

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $secret = $googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($secret);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('init/index.html.twig', [
            'user' => $user,
        ]);
    }

}
