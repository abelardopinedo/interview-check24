<?php

namespace App\Entity;

use App\Repository\RequestLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestLogRepository::class)]
class RequestLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requestPayload = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $responsePayload = null;

    #[ORM\Column(nullable: true)]
    private ?int $latency = null;

    #[ORM\Column(length: 255)]
    private string $endpoint;

    #[ORM\Column(length: 10)]
    private string $httpMethod;

    #[ORM\Column(nullable: true)]
    private ?int $statusCode = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestPayload(): ?string
    {
        return $this->requestPayload;
    }

    public function setRequestPayload(?string $requestPayload): static
    {
        $this->requestPayload = $requestPayload;

        return $this;
    }

    public function getResponsePayload(): ?string
    {
        return $this->responsePayload;
    }

    public function setResponsePayload(?string $responsePayload): static
    {
        $this->responsePayload = $responsePayload;

        return $this;
    }

    public function getLatency(): ?int
    {
        return $this->latency;
    }

    public function setLatency(?int $latency): static
    {
        $this->latency = $latency;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getHttpMethod(): ?string
    {
        return $this->httpMethod;
    }

    public function setHttpMethod(string $httpMethod): static
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
