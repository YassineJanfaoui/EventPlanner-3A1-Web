<?php

namespace App\DataFixtures;

use App\Entity\Bill;
use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;

class BillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $event = $manager->getRepository(Event::class)->find(2);


        $statuses = ['pending', 'paid'];

        for ($i = 1; $i <= 5; $i++) {
            $bill = new Bill();
            $bill->setDueDate(new DateTime("+" . rand(1, 30) . " days"));
            $bill->setPaymentStatus($statuses[array_rand($statuses)]);
            $bill->setAmount(rand(100, 5000));
            $bill->setDescription("Invoice for services #$i");
            $bill->setArchived(rand(0, 1));

            $bill->setEvent($event);

            $manager->persist($bill);
        }

        $manager->flush();
    }
}
