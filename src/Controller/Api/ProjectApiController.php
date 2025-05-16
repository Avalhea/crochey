<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/projects')]
final class ProjectApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    #[Route('', name: 'api_project_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();
        $data = $this->serializer->serialize($projects, 'json', ['groups' => ['project:read']]);
        
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_project_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $project = new Project();
        
        // Add your property mapping here
        $project->setName($data['name'] ?? '');
        // Add other properties...
        
        $this->entityManager->persist($project);
        $this->entityManager->flush();
        
        $jsonProject = $this->serializer->serialize($project, 'json', ['groups' => ['project:read']]);
        return new JsonResponse($jsonProject, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_project_show', methods: ['GET'])]
    public function show(Project $project): JsonResponse
    {
        $data = $this->serializer->serialize($project, 'json', ['groups' => ['project:read']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_project_update', methods: ['PUT'])]
    public function update(Request $request, Project $project): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Add your property mapping here
        $project->setName($data['name'] ?? $project->getName());
        // Update other properties...
        
        $this->entityManager->flush();
        
        $jsonProject = $this->serializer->serialize($project, 'json', ['groups' => ['project:read']]);
        return new JsonResponse($jsonProject, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(Project $project): JsonResponse
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
} 