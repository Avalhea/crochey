<?php

namespace App\Tests\Controller;

use App\Entity\Yarn;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class YarnControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $yarnRepository;
    private string $path = '/yarn/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->yarnRepository = $this->manager->getRepository(Yarn::class);

        foreach ($this->yarnRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Yarn index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'yarn[name]' => 'Testing',
            'yarn[color]' => 'Testing',
            'yarn[brand]' => 'Testing',
            'yarn[quantity]' => 'Testing',
            'yarn[imageUrl]' => 'Testing',
            'yarn[notes]' => 'Testing',
            'yarn[addedAt]' => 'Testing',
            'yarn[FiberContent]' => 'Testing',
            'yarn[Weight]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->yarnRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Yarn();
        $fixture->setName('My Title');
        $fixture->setColor('My Title');
        $fixture->setBrand('My Title');
        $fixture->setQuantity('My Title');
        $fixture->setImageUrl('My Title');
        $fixture->setNotes('My Title');
        $fixture->setAddedAt('My Title');
        $fixture->setFiberContent('My Title');
        $fixture->setWeight('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Yarn');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Yarn();
        $fixture->setName('Value');
        $fixture->setColor('Value');
        $fixture->setBrand('Value');
        $fixture->setQuantity('Value');
        $fixture->setImageUrl('Value');
        $fixture->setNotes('Value');
        $fixture->setAddedAt('Value');
        $fixture->setFiberContent('Value');
        $fixture->setWeight('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'yarn[name]' => 'Something New',
            'yarn[color]' => 'Something New',
            'yarn[brand]' => 'Something New',
            'yarn[quantity]' => 'Something New',
            'yarn[imageUrl]' => 'Something New',
            'yarn[notes]' => 'Something New',
            'yarn[addedAt]' => 'Something New',
            'yarn[FiberContent]' => 'Something New',
            'yarn[Weight]' => 'Something New',
        ]);

        self::assertResponseRedirects('/yarn/');

        $fixture = $this->yarnRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getColor());
        self::assertSame('Something New', $fixture[0]->getBrand());
        self::assertSame('Something New', $fixture[0]->getQuantity());
        self::assertSame('Something New', $fixture[0]->getImageUrl());
        self::assertSame('Something New', $fixture[0]->getNotes());
        self::assertSame('Something New', $fixture[0]->getAddedAt());
        self::assertSame('Something New', $fixture[0]->getFiberContent());
        self::assertSame('Something New', $fixture[0]->getWeight());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Yarn();
        $fixture->setName('Value');
        $fixture->setColor('Value');
        $fixture->setBrand('Value');
        $fixture->setQuantity('Value');
        $fixture->setImageUrl('Value');
        $fixture->setNotes('Value');
        $fixture->setAddedAt('Value');
        $fixture->setFiberContent('Value');
        $fixture->setWeight('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/yarn/');
        self::assertSame(0, $this->yarnRepository->count([]));
    }
}
