<?php

namespace App\Tests\Controller;

use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProviderControllerTest extends WebTestCase
{
    public function testListProviders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/providers');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testGetProviderNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/providers/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearchProvider(): void
    {
        $client = static::createClient();

        // Search for something that likely exists or just check structure
        $client->request('GET', '/providers/search?q=Provider');

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testSearchProviderMissingQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/providers/search');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateProvider(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $repo = $container->get(ProviderRepository::class);
        $em = $container->get('doctrine.orm.entity_manager');

        // Ensure we have at least one provider to update
        $provider = $repo->findOneBy([]);
        if (!$provider) {
            $provider = new Provider();
            $provider->setName('Test Provider');
            $provider->setUrl('https://test.com');
            $provider->setHasDiscount(false);
            $em->persist($provider);
            $em->flush();
        }

        $id = $provider->getId();
        $newName = 'Updated Name ' . uniqid();

        $client->request(
            'PATCH',
            '/providers/' . $id,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => $newName,
                'has_discount' => true
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($newName, $response['name']);
        $this->assertTrue($response['has_discount']);
    }

    public function testUpdateProviderInternalKeyShouldNotChange(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $repo = $container->get(ProviderRepository::class);
        $em = $container->get('doctrine.orm.entity_manager');

        $provider = $repo->findOneBy([]);
        $id = $provider->getId();
        $originalInternalKey = $provider->getInternalKey();

        $client->request(
            'PATCH',
            '/providers/' . $id,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'internal_key' => 'hacky_key',
                'name' => 'Name change'
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($originalInternalKey, $response['internal_key']);
        $this->assertEquals('Name change', $response['name']);
    }
}
