<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/tags')]
final class TagApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagRepository $tagRepository,
    ) {
    }

    #[Route('', name: 'api_tag_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tags = $this->tagRepository->findAll();
        $data = $this->serializer->serialize($tags, 'json', ['groups' => ['tag:read']]);
        
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_tag_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $tag = new Tag();
            
            // Set the label
            $tag->setLabel($data['label'] ?? '');
            
            // Set the project if it exists in the request
            if (isset($data['project']) && isset($data['project']['id'])) {
                $project = $this->entityManager->getRepository(\App\Entity\Project::class)->find($data['project']['id']);
                if (!$project) {
                    return new JsonResponse(['error' => 'Project not found'], Response::HTTP_BAD_REQUEST);
                }
                $tag->setProject($project);
            } else {
                return new JsonResponse(['error' => 'Project is required'], Response::HTTP_BAD_REQUEST);
            }
            
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
            
            $jsonTag = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);
            return new JsonResponse($jsonTag, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_tag_show', methods: ['GET'])]
    public function show(Tag $tag): JsonResponse
    {
        $data = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_tag_update', methods: ['PUT'])]
    public function update(Request $request, Tag $tag): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Update the label
            if (isset($data['label'])) {
                $tag->setLabel($data['label']);
            }
            
            // Update the project if it exists in the request
            if (isset($data['project']) && isset($data['project']['id'])) {
                $project = $this->entityManager->getRepository(\App\Entity\Project::class)->find($data['project']['id']);
                if (!$project) {
                    return new JsonResponse(['error' => 'Project not found'], Response::HTTP_BAD_REQUEST);
                }
                $tag->setProject($project);
            }
            
            $this->entityManager->flush();
            
            $jsonTag = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);
            return new JsonResponse($jsonTag, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_tag_delete', methods: ['DELETE'])]
    public function delete(Tag $tag): JsonResponse
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/project/{projectId}', name: 'api_tags_by_project', methods: ['GET'])]
    public function getTagsByProject(int $projectId): JsonResponse
    {
        $tags = $this->tagRepository->findBy(['project' => $projectId]);
        $data = $this->serializer->serialize($tags, 'json', ['groups' => ['tag:read']]);
        
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
} 