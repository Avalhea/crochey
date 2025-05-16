<?php

namespace App\Controller;

use App\Entity\ProjectImage;
use App\Form\ProjectImageType;
use App\Repository\ProjectImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project/image')]
final class ProjectImageController extends AbstractController
{
    #[Route(name: 'app_project_image_index', methods: ['GET'])]
    public function index(ProjectImageRepository $projectImageRepository): Response
    {
        return $this->render('project_image/index.html.twig', [
            'project_images' => $projectImageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_project_image_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projectImage = new ProjectImage();
        $form = $this->createForm(ProjectImageType::class, $projectImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectImage);
            $entityManager->flush();
            return $this->redirectToRoute('app_project_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_image/new.html.twig', [
            'project_image' => $projectImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_image_show', methods: ['GET'])]
    public function show(ProjectImage $projectImage): Response
    {
        return $this->render('project_image/show.html.twig', [
            'project_image' => $projectImage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_image_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProjectImage $projectImage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectImageType::class, $projectImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_project_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_image/edit.html.twig', [
            'project_image' => $projectImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_image_delete', methods: ['POST'])]
    public function delete(Request $request, ProjectImage $projectImage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projectImage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($projectImage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
