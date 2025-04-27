<?php

namespace App\Controller;

use App\Entity\Yarn;
use App\Form\YarnType;
use App\Repository\YarnRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/yarn')]
final class YarnController extends AbstractController
{
    #[Route(name: 'app_yarn_index', methods: ['GET'])]
    public function index(YarnRepository $yarnRepository): Response
    {
        return $this->render('yarn/index.html.twig', [
            'yarns' => $yarnRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_yarn_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $yarn = new Yarn();
        $form = $this->createForm(YarnType::class, $yarn);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($yarn);
            $entityManager->flush();

            return $this->redirectToRoute('app_yarn_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('yarn/new.html.twig', [
            'yarn' => $yarn,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_yarn_show', methods: ['GET'])]
    public function show(Yarn $yarn): Response
    {
        return $this->render('yarn/show.html.twig', [
            'yarn' => $yarn,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_yarn_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Yarn $yarn, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(YarnType::class, $yarn);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_yarn_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('yarn/edit.html.twig', [
            'yarn' => $yarn,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_yarn_delete', methods: ['POST'])]
    public function delete(Request $request, Yarn $yarn, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$yarn->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($yarn);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_yarn_index', [], Response::HTTP_SEE_OTHER);
    }
}
