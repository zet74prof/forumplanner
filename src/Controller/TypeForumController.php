<?php

namespace App\Controller;

use App\Entity\TypeForum;
use App\Form\TypeForumType;
use App\Repository\TypeForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/typeforum')]
final class TypeForumController extends AbstractController
{
    #[Route(name: 'app_type_forum_index', methods: ['GET'])]
    public function index(TypeForumRepository $typeForumRepository): Response
    {
        return $this->render('type_forum/index.html.twig', [
            'type_forums' => $typeForumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeForum = new TypeForum();
        $form = $this->createForm(TypeForumType::class, $typeForum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeForum);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_forum/new.html.twig', [
            'type_forum' => $typeForum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_forum_show', methods: ['GET'])]
    public function show(TypeForum $typeForum): Response
    {
        return $this->render('type_forum/show.html.twig', [
            'type_forum' => $typeForum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeForum $typeForum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeForumType::class, $typeForum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_forum/edit.html.twig', [
            'type_forum' => $typeForum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_forum_delete', methods: ['POST'])]
    public function delete(Request $request, TypeForum $typeForum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeForum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeForum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_forum_index', [], Response::HTTP_SEE_OTHER);
    }
}
