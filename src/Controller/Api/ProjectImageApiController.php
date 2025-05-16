<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Entity\ProjectImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/project/image')]
final class ProjectImageApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'api_project_image_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Extract project ID from IRI
            $projectId = (int) substr($data['project'], strrpos($data['project'], '/') + 1);
            $project = $this->entityManager->getRepository(Project::class)->find($projectId);
            
            if (!$project) {
                throw new \Exception('Project not found');
            }
            
            $projectImage = new ProjectImage();
            $projectImage->setImageUrl($data['imageUrl']);
            $projectImage->setCaption($data['caption']);
            $projectImage->setProject($project);
            
            $this->entityManager->persist($projectImage);
            $this->entityManager->flush();
            
            return new JsonResponse(
                $this->serializer->serialize($projectImage, 'json', ['groups' => ['project:read']]),
                Response::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_project_image_update', methods: ['PUT'])]
    public function update(Request $request, ProjectImage $projectImage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $projectImage->setImageUrl($data['imageUrl']);
            $projectImage->setCaption($data['caption']);
            
            $this->entityManager->flush();
            
            return new JsonResponse(
                $this->serializer->serialize($projectImage, 'json', ['groups' => ['project:read']]),
                Response::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_project_image_delete', methods: ['DELETE'])]
    public function delete(ProjectImage $projectImage): JsonResponse
    {
        try {
            $this->entityManager->remove($projectImage);
            $this->entityManager->flush();
            
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
} 