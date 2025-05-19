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
        try {
            $data = json_decode($request->getContent(), true);
            $project = new Project();
            
            // Add your property mapping here
            $project->setName($data['name'] ?? '');
            $project->setDescription($data['description'] ?? '');
            $project->setStatus(\App\Enum\Status::from($data['Status'] ?? 'Not started'));
            $project->setDifficulty(\App\Enum\Difficulty::from($data['Difficulty'] ?? 'Beginner'));
            $project->setImageUrl($data['imageUrl'] ?? null);
            
            // Handle dates based on status and form input
            if (isset($data['started_at']) && $data['started_at']) {
                $project->setStartedAt(new \DateTimeImmutable($data['started_at']));
            } else if ($project->getStatus() === \App\Enum\Status::WIP || $project->getStatus() === \App\Enum\Status::FINISHED) {
                $project->setStartedAt(new \DateTimeImmutable());
            }
            
            if (isset($data['finished_at']) && $data['finished_at']) {
                $project->setFinishedAt(new \DateTimeImmutable($data['finished_at']));
            } else if ($project->getStatus() === \App\Enum\Status::FINISHED) {
                $project->setFinishedAt(new \DateTimeImmutable());
            }
            
            // Handle tags if they are included in the request
            if (isset($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tagData) {
                    if (is_string($tagData)) {
                        // Handle case where tag is just a string label
                        $tag = new \App\Entity\Tag();
                        $tag->setLabel($tagData);
                        $tag->setProject($project);
                    } else {
                        // Handle case where tag is an object with label property
                        $tag = new \App\Entity\Tag();
                        $tag->setLabel($tagData['label'] ?? '');
                        $tag->setProject($project);
                    }
                    $this->entityManager->persist($tag);
                }
            }
            
            $this->entityManager->persist($project);
            $this->entityManager->flush();
            
            $jsonProject = $this->serializer->serialize($project, 'json', ['groups' => ['project:read']]);
            return new JsonResponse($jsonProject, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
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
        try {
            $data = json_decode($request->getContent(), true);
            
            // Update all properties
            $project->setName($data['name'] ?? $project->getName());
            $project->setDescription($data['description'] ?? $project->getDescription());
            $project->setStatus(\App\Enum\Status::from($data['Status'] ?? $project->getStatus()->value));
            $project->setDifficulty(\App\Enum\Difficulty::from($data['Difficulty'] ?? $project->getDifficulty()->value));
            $project->setImageUrl($data['imageUrl'] ?? $project->getImageUrl());
            
            // Handle dates based on status and form input
            if (isset($data['started_at'])) {
                $project->setStartedAt($data['started_at']);
            }
            if (isset($data['finished_at'])) {
                $project->setFinishedAt($data['finished_at']);
            }
            
            // Handle tags if they are included in the request
            if (isset($data['tags']) && is_array($data['tags'])) {
                // Remove existing tags
                foreach ($project->getTags() as $existingTag) {
                    $this->entityManager->remove($existingTag);
                }
                
                // Add new tags
                foreach ($data['tags'] as $tagData) {
                    if (is_string($tagData)) {
                        // Handle case where tag is just a string label
                        $tag = new \App\Entity\Tag();
                        $tag->setLabel($tagData);
                        $tag->setProject($project);
                    } else {
                        // Handle case where tag is an object with label property
                        $tag = new \App\Entity\Tag();
                        $tag->setLabel($tagData['label'] ?? '');
                        $tag->setProject($project);
                    }
                    $this->entityManager->persist($tag);
                }
            }
            
            $this->entityManager->flush();
            
            $jsonProject = $this->serializer->serialize($project, 'json', ['groups' => ['project:read']]);
            return new JsonResponse($jsonProject, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(Project $project): JsonResponse
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/search', name: 'api_project_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (!empty($query)) {
            $projects = $this->projectRepository->searchByNameDescriptionOrTag($query);
        } else {
            $projects = $this->projectRepository->findAll();
        }
        
        $data = $this->serializer->serialize($projects, 'json', ['groups' => ['project:read']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
} 