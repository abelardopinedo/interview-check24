<?php

namespace App\Service\Provider;

use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Base class for all insurance providers.
 * Handles common configuration loading from the database.
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected ?string $url = null;
    protected ?bool $hasDiscount = null;
    protected ?Provider $providerEntity = null;

    abstract public function getInternalKey(): string;

    public function __construct(
        protected HttpClientInterface $client,
        protected ProviderRepository $repository,
        protected SerializerInterface $serializer
    ) {
        $provider = $this->repository->findOneBy(['internalKey' => $this->getInternalKey()]);
        if ($provider) {
            $this->url = $provider->getUrl();
            $this->hasDiscount = $provider->isHasDiscount();
            $this->providerEntity = $provider;
        }
    }

    public function getUrl(): string
    {
        return $this->url ?? '';
    }

    public function getName(): string
    {
        if ($this->providerEntity) {
            return $this->providerEntity->getName();
        }

        // Fallback to snake_case class name if entity not loaded
        $reflect = new \ReflectionClass($this);
        $shortName = $reflect->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }

    public function hasCampaignDiscount(): bool
    {
        return $this->hasDiscount ?? false;
    }

    public function getProviderEntity(): Provider
    {
        if (!$this->providerEntity) {
            throw new \RuntimeException(sprintf('Provider entity for "%s" not found in database.', $this->getName()));
        }
        return $this->providerEntity;
    }
}
