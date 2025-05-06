<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EquipmentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'tools',
            'IT',
            'accomodation',
            'Audio & Visual',
            'Transport'
        ];
        $states = ['functional', 'maintenance', 'unavailable'];

        for ($i = 1; $i <= 10; $i++) {
            $equipment = new Equipment();
            $equipment->setName("Equipment $i");
            $equipment->setCategory($categories[array_rand($categories)]);
            $equipment->setState($states[array_rand($states)]);
            $equipment->setQuantity(rand(1, 100));
            $equipment->setPrice(strval(rand(10, 1000)));

            $manager->persist($equipment);
        }

        $manager->flush();
    }
}
