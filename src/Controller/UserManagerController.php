<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRolesType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserManagerController extends AbstractController
{
    #[Route('/usermanager', name: 'app_user_manager')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $users = $doctrine->getRepository(User::class)->findAll();


        return $this->render('user_manager/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/usermanager/{id}', name: 'app_user_manager_change_role')]
    public function changeRole(User $user, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            return $this->redirectToRoute('app_user_manager_change_role', ['id' => $user->getId()]);
        }

        return $this->render('user_manager/change_role.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
