<?php

namespace App\Tests\Controller;

use App\Entity\ProjectImage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectImageControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $projectImageRepository;
    private string $path = '/project/image/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->projectImageRepository = $this->manager->getRepository(ProjectImage::class);

        foreach ($this->projectImageRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ProjectImage index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'project_image[imageUrl]' => 'Testing',
            'project_image[caption]' => 'Testing',
            'project_image[project]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->projectImageRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new ProjectImage();
        $fixture->setImageUrl('My Title');
        $fixture->setCaption('My Title');
        $fixture->setProject('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ProjectImage');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new ProjectImage();
        $fixture->setImageUrl('Value');
        $fixture->setCaption('Value');
        $fixture->setProject('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'project_image[imageUrl]' => 'Something New',
            'project_image[caption]' => 'Something New',
            'project_image[project]' => 'Something New',
        ]);

        self::assertResponseRedirects('/project/image/');

        $fixture = $this->projectImageRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getImageUrl());
        self::assertSame('Something New', $fixture[0]->getCaption());
        self::assertSame('Something New', $fixture[0]->getProject());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new ProjectImage();
        $fixture->setImageUrl('Value');
        $fixture->setCaption('Value');
        $fixture->setProject('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/project/image/');
        self::assertSame(0, $this->projectImageRepository->count([]));
    }
}
