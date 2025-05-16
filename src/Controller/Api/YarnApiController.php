<?php

namespace App\Controller\Api;

use App\Entity\Yarn;
use App\Repository\YarnRepository;
use App\Enum\FiberContent;
use App\Enum\YarnWeight;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/yarns')]
final class YarnApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly YarnRepository $yarnRepository,
    ) {
    }

    #[Route('', name: 'api_yarn_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $yarns = $this->yarnRepository->findAll();
        $data = $this->serializer->serialize($yarns, 'json', ['groups' => ['yarn:read']]);
        
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_yarn_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $yarn = new Yarn();
            
            // Always set added_at for new yarns
            $yarn->setAddedAt(new \DateTimeImmutable());
            $this->updateYarnFromData($yarn, $data);
            $this->entityManager->persist($yarn);
            $this->entityManager->flush();
            
            $jsonYarn = $this->serializer->serialize($yarn, 'json', ['groups' => ['yarn:read']]);
            return new JsonResponse($jsonYarn, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_yarn_show', methods: ['GET'])]
    public function show(Yarn $yarn): JsonResponse
    {
        $data = $this->serializer->serialize($yarn, 'json', ['groups' => ['yarn:read']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_yarn_update', methods: ['PUT'])]
    public function update(Request $request, Yarn $yarn): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $this->updateYarnFromData($yarn, $data);
        
        $this->entityManager->flush();
        
        $jsonYarn = $this->serializer->serialize($yarn, 'json', ['groups' => ['yarn:read']]);
        return new JsonResponse($jsonYarn, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_yarn_delete', methods: ['DELETE'])]
    public function delete(Yarn $yarn): JsonResponse
    {
        $this->entityManager->remove($yarn);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function updateYarnFromData(Yarn $yarn, array $data): void
    {
        if (isset($data['name'])) {
            $yarn->setName($data['name']);
        }
        
        if (isset($data['brand'])) {
            $yarn->setBrand($data['brand']);
        }
        
        if (isset($data['color'])) {
            $yarn->setColor($data['color']);
        }
        
        if (isset($data['quantity'])) {
            $yarn->setQuantity((int) $data['quantity']);
        }
        
        if (isset($data['imageUrl'])) {
            $yarn->setImageUrl($data['imageUrl']);
        }
        
        if (isset($data['notes'])) {
            $yarn->setNotes($data['notes']);
        }
        
        if (isset($data['Weight'])) {
            $yarn->setWeight(YarnWeight::from($data['Weight']));
        }
        
        if (isset($data['FiberContent'])) {
            $yarn->setFiberContent(FiberContent::from($data['FiberContent']));
        }
    }
} 