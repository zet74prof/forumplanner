<?php

namespace App\Controller;

use App\Form\CheckAuthenticatorCodeType;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/secprofile', name: 'app_security_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request): Response
    {
        //change password
        if ($request->request->get('password') && $request->request->get('password') === $request->request->get('password2')) {
            $user = $this->getUser();
            $user->setPassword($request->request->get('password'));
            //$this->getDoctrine()->getManager()->flush();
        }


        return $this->render('security/profile.html.twig', [
        ]);
    }

    #[Route(path: '/enable2fa', name: 'app_2fa_enable')]
    #[IsGranted('ROLE_USER')]
    public function enable2fa(Request $request, EntityManagerInterface $entityManager, GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        $connectedUser = $this->getUser();

        if (!($connectedUser instanceof GoogleAuthenticatorTwoFactorInterface)) {
            throw new NotFoundHttpException('Cannot display QR code');
        }

        $form = $this->createForm(CheckAuthenticatorCodeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('authenticatorCode')->getData();
            if ($googleAuthenticator->checkCode($connectedUser, strval($code))) {
                $this->addFlash('success', 'Authentification à 2 facteurs activée avec succès.');
            } else {
                $connectedUser->setGoogleAuthenticatorSecret(null);
                $entityManager->persist($connectedUser);
                $entityManager->flush();
                $this->addFlash('danger', 'Code invalide. L\'authentification à 2 facteurs n\'a pas pu être activée. Flashez à nouveau le QR code.');
            }
            return $this->redirectToRoute('app_2fa_enable');
        } else {
            if ($connectedUser->isGoogleAuthenticatorEnabled()) {
                if (count($request->getSession()->getFlashBag()->all()) === 0){
                    $this->addFlash('info', 'Authentification à 2 facteurs déjà activée.');
                }
            } else {
                $secret = $googleAuthenticator->generateSecret();
                $connectedUser->setGoogleAuthenticatorSecret($secret);
                $entityManager->persist($connectedUser);
                $entityManager->flush();
            }
        }

        $qrCodeContent = $googleAuthenticator->getQRContent($connectedUser);

        return $this->render('security/enable2fa.html.twig', [
            'qrCodeContent' => $qrCodeContent,
            'form' => $form,
        ]);
    }
}
