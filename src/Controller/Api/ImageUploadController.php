<?php

namespace App\Controller\Api;

use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/image')]
final class ImageUploadController extends AbstractController
{
    public function __construct(
        private FileUploader $fileUploader
    ) {}

    #[Route('/upload', name: 'api_image_upload', methods: ['POST'])]
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
} 