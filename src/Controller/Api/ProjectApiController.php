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
use App\Service\FileUploader;

#[Route('/api/projects')]
final class ProjectApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectRepository $projectRepository,
        private readonly FileUploader $fileUploader,
    ) {
    }

    private function deleteImageFile(string $imageUrl): void
    {
        try {
            // Extract filename from URL
            $filename = basename($imageUrl);
            if ($filename) {
                $this->fileUploader->delete($filename);
            }
        } catch (\Exception $e) {
            error_log('Error deleting image file: ' . $e->getMessage());
        }
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
            
            // Handle image update
            if (isset($data['imageUrl']) && $data['imageUrl'] !== $project->getImageUrl()) {
                // Delete old image if it exists
                if ($project->getImageUrl()) {
                    $this->deleteImageFile($project->getImageUrl());
                }
                $project->setImageUrl($data['imageUrl']);
            }
            
            // Update all properties
            $project->setName($data['name'] ?? $project->getName());
            $project->setDescription($data['description'] ?? $project->getDescription());
            $project->setStatus(\App\Enum\Status::from($data['Status'] ?? $project->getStatus()->value));
            $project->setDifficulty(\App\Enum\Difficulty::from($data['Difficulty'] ?? $project->getDifficulty()->value));
            
            // Handle dates based on status and form input
            if (isset($data['started_at'])) {
                $project->setStartedAt($data['started_at']);
            }
            if (isset($data['finished_at'])) {
                $project->setFinishedAt($data['finished_at']);
            }
            
            // Handle tags if they are included in the request
            if (isset($data['tags']) && is_array($data['tags'])) {
                // Get existing tags for this project
                $existingTags = $project->getTags();
                $existingTagLabels = [];
                foreach ($existingTags as $tag) {
                    $existingTagLabels[$tag->getLabel()] = $tag;
                }
                
                // Process new tags
                $newTags = [];
                foreach ($data['tags'] as $tagData) {
                    $tagLabel = is_string($tagData) ? $tagData : ($tagData['label'] ?? '');
                    
                    if (empty($tagLabel)) {
                        continue;
                    }
                    
                    // If tag already exists, reuse it
                    if (isset($existingTagLabels[$tagLabel])) {
                        $newTags[] = $existingTagLabels[$tagLabel];
                        unset($existingTagLabels[$tagLabel]); // Remove from existing tags
                    } else {
                        // Create new tag
                        $tag = new \App\Entity\Tag();
                        $tag->setLabel($tagLabel);
                        $tag->setProject($project);
                        $this->entityManager->persist($tag);
                        $newTags[] = $tag;
                    }
                }
                
                // Remove tags that are no longer used
                foreach ($existingTagLabels as $tag) {
                    $project->removeTag($tag);
                    $this->entityManager->remove($tag);
                }
                
                // Add all new tags
                foreach ($newTags as $tag) {
                    $project->addTag($tag);
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
        try {
            // Delete main project image if it exists
            if ($project->getImageUrl()) {
                $this->deleteImageFile($project->getImageUrl());
            }
            
            // Delete all project images
            foreach ($project->getProjectImage() as $projectImage) {
                if ($projectImage->getImageUrl()) {
                    $this->deleteImageFile($projectImage->getImageUrl());
                }
            }
            
            $this->entityManager->remove($project);
            $this->entityManager->flush();
            
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
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