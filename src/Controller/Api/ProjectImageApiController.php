<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Service\FileUploader;
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
        private SerializerInterface $serializer,
        private FileUploader $fileUploader
    ) {}

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

    #[Route('', name: 'api_project_image_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // Log request details
            error_log('Content-Type: ' . $request->headers->get('Content-Type'));
            error_log('Request method: ' . $request->getMethod());
            error_log('Files: ' . print_r($request->files->all(), true));
            error_log('Request data: ' . print_r($request->request->all(), true));
            
            $projectId = null;
            $caption = '';
            $uploadedFile = $request->files->get('image');
            
            // Handle JSON request
            if ($request->getContentTypeFormat() === 'json') {
                $data = json_decode($request->getContent(), true);
                error_log('JSON data: ' . print_r($data, true));
                
                $projectId = $data['projectId'] ?? null;
                $caption = $data['caption'] ?? '';
            } else {
                // Handle form data
                $projectId = $request->request->get('projectId');
                $caption = $request->request->get('caption', '');
            }
            
            if (!$projectId) {
                throw new \Exception('Project ID is required');
            }
            
            $project = $this->entityManager->getRepository(Project::class)->find($projectId);
            
            if (!$project) {
                throw new \Exception('Project not found');
            }
            
            $projectImage = new ProjectImage();
            $projectImage->setCaption($caption);
            $projectImage->setProject($project);
            
            // Handle file upload
            if ($uploadedFile) {
                $fileName = $this->fileUploader->upload($uploadedFile);
                $imageUrl = $request->getSchemeAndHttpHost() . '/uploads/' . $fileName;
                $projectImage->setImageUrl($imageUrl);
            } 
            // Handle URL-based image
            else if ($request->getContentTypeFormat() === 'json') {
                $data = json_decode($request->getContent(), true);
                if (!isset($data['imageUrl'])) {
                    throw new \Exception('No image URL provided');
                }
                $projectImage->setImageUrl($data['imageUrl']);
            } else {
                throw new \Exception('No image file or URL provided');
            }
            
            $this->entityManager->persist($projectImage);
            $this->entityManager->flush();
            
            return new JsonResponse(
                $this->serializer->serialize($projectImage, 'json', ['groups' => ['project:read']]),
                Response::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            error_log('Error in create: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/upload', name: 'api_project_image_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        try {
            $uploadedFile = $request->files->get('image');
            
            if (!$uploadedFile) {
                throw new \Exception('No image file uploaded');
            }
            
            // Upload the file
            $fileName = $this->fileUploader->upload($uploadedFile);
            $imageUrl = $request->getSchemeAndHttpHost() . '/uploads/' . $fileName;
            
            return new JsonResponse([
                'imageUrl' => $imageUrl
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_project_image_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, ProjectImage $projectImage): JsonResponse
    {
        try {
            $oldImageUrl = $projectImage->getImageUrl();
            
            // Handle both JSON and form data
            if ($request->getContentTypeFormat() === 'json') {
                $data = json_decode($request->getContent(), true);
                $caption = $data['caption'] ?? $projectImage->getCaption();
                $imageUrl = $data['imageUrl'] ?? $projectImage->getImageUrl();
            } else {
                $caption = $request->request->get('caption', $projectImage->getCaption());
                $imageUrl = $projectImage->getImageUrl();
                
                $uploadedFile = $request->files->get('image');
                if ($uploadedFile) {
                    // Delete old image if it exists
                    if ($oldImageUrl) {
                        $this->deleteImageFile($oldImageUrl);
                    }
                    
                    $fileName = $this->fileUploader->upload($uploadedFile);
                    $imageUrl = $request->getSchemeAndHttpHost() . '/uploads/' . $fileName;
                }
            }
            
            $projectImage->setImageUrl($imageUrl);
            $projectImage->setCaption($caption);
            
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
            // Delete the image file before removing the entity
            $imageUrl = $projectImage->getImageUrl();
            if ($imageUrl) {
                $this->deleteImageFile($imageUrl);
            }
            
            $this->entityManager->remove($projectImage);
            $this->entityManager->flush();
            
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
} 