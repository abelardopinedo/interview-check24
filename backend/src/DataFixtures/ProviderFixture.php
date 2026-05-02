<?php

namespace App\DataFixtures;

use App\Entity\Provider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProviderFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $providersData = [
            [
                'name' => 'Provider A',
                'url' => 'http://provider-a:8001/provider-a/quote',
                'key' => 'provider_a',
                'discount' => true
            ],
            [
                'name' => 'Provider B',
                'url' => 'http://provider-b:8002/provider-b/quote',
                'key' => 'provider_b',
                'discount' => false
            ],
            [
                'name' => 'Provider C',
                'url' => 'http://provider-c:8003/provider-c/quote',
                'key' => 'provider_c',
                'discount' => true
            ],
        ];

        foreach ($providersData as $data) {
            $provider = new Provider();
            $provider->setName($data['name']);
            $provider->setUrl($data['url']);
            $provider->setInternalKey($data['key']);
            $provider->setHasDiscount($data['discount']);

            $manager->persist($provider);
        }

        $manager->flush();
    }
}