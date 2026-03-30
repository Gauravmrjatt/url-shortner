<?php

class Url {
    private $id;
    private $originalUrl;
    private $shortCode;
    private $customAlias;
    private $clicks;
    private $createdAt;
    private $expiresAt;
    private $isActive;
    private $userId;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->originalUrl = $data['original_url'] ?? '';
            $this->shortCode = $data['short_code'] ?? '';
            $this->customAlias = $data['custom_alias'] ?? null;
            $this->clicks = $data['clicks'] ?? 0;
            $this->createdAt = $data['created_at'] ?? null;
            $this->expiresAt = $data['expires_at'] ?? null;
            $this->isActive = $data['is_active'] ?? 1;
            $this->userId = $data['user_id'] ?? null;
        }
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getOriginalUrl(): string {
        return $this->originalUrl;
    }

    public function getShortCode(): string {
        return $this->shortCode;
    }

    public function getCustomAlias(): ?string {
        return $this->customAlias;
    }

    public function getClicks(): int {
        return $this->clicks;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?string {
        return $this->expiresAt;
    }

    public function isActive(): bool {
        return $this->isActive == 1;
    }

    public function isExpired(): bool {
        if ($this->expiresAt === null) {
            return false;
        }
        return strtotime($this->expiresAt) < time();
    }

    public function getUserId(): ?string {
        return $this->userId;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'original_url' => $this->originalUrl,
            'short_code' => $this->shortCode,
            'custom_alias' => $this->customAlias,
            'clicks' => $this->clicks,
            'created_at' => $this->createdAt,
            'expires_at' => $this->expiresAt,
            'is_active' => $this->isActive,
            'user_id' => $this->userId
        ];
    }

    public function toApiArray(): array {
        return [
            'original_url' => $this->originalUrl,
            'short_code' => $this->shortCode,
            'short_url' => $this->getShortUrl(),
            'clicks' => $this->clicks,
            'created_at' => $this->createdAt,
            'expires_at' => $this->expiresAt
        ];
    }

    public function getShortUrl(): string {
        $code = !empty($this->customAlias) ? $this->customAlias : $this->shortCode;
        return BASE_URL . '/s/' . $code;
    }
}
