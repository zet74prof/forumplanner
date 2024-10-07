<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Stand;
use App\Form\ForumType;
use App\Form\StandType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/forum')]
final class ForumController extends AbstractController
{
    #[Route(name: 'app_forum_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ForumRepository $forumRepository): Response
    {
        $user = $this->getUser();
        $forums = $forumRepository->findBy(['user' => $user]);

        return $this->render('forum/index.html.twig', [
            'forums' => $forums,
        ]);
    }

    #[Route('/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $forum = new Forum();
        $forum->setUser($user);

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($forum);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/new.html.twig', [
            'forum' => $forum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {

        $stand = new Stand();
        $stand->setForum($forum);
        $standForm = $this->createForm(StandType::class, $stand);
        $standForm->handleRequest($request);

        if ($standForm->isSubmitted() && $standForm->isValid()) {
            $entityManager->persist($stand);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_show', ['id' => $forum->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/show.html.twig', [
            'forum' => $forum,
            'standForm' => $standForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/edit.html.twig', [
            'forum' => $forum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($forum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
    }
}
