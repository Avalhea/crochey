<?php
// src/DataFixtures/ProjectFixtures.php
namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Enum\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProjectFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $statusCases = Status::cases();

        // Génère 10 projets
        for ($i = 0; $i < 10; $i++) {
            $project = new Project();
            $project->setName($faker->sentence(3));
            $project->setDescription($faker->paragraph());
            $project->setImageUrl($faker->imageUrl());
            $project->setStatus($statusCases[array_rand($statusCases)]);
            $manager->persist($project);

            // Pour chaque projet : 1 à 3 images associées
            $nbImages = rand(1, 3);
            for ($j = 0; $j < $nbImages; $j++) {
                $img = new ProjectImage();
                $img->setProject($project);
                $img->setCaption($faker->text());
                $img->setImageUrl($faker->imageUrl(640, 480, 'tech'));
                $manager->persist($img);
            }
        }

        $manager->flush();
    }
}
