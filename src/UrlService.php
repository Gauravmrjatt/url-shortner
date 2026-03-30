<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Url.php';

class UrlService {
    private $db;
    private const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createShortUrl(string $originalUrl, ?string $customAlias = null, ?string $userId = null, ?string $expiresAt = null): array {
        $result = [
            'success' => false,
            'data' => null,
            'error' => null
        ];

        $originalUrl = trim($originalUrl);
        
        if (!$this->isValidUrl($originalUrl)) {
            $result['error'] = 'Invalid URL format';
            return $result;
        }

        if (!empty($customAlias)) {
            $customAlias = trim($customAlias);
            if (!$this->isValidAlias($customAlias)) {
                $result['error'] = 'Invalid alias format. Use only letters, numbers, and hyphens (3-50 characters)';
                return $result;
            }
            if ($this->aliasExists($customAlias)) {
                $result['error'] = 'This alias is already taken';
                return $result;
            }
            $shortCode = $customAlias;
        } else {
            $shortCode = $this->generateShortCode();
            while ($this->codeExists($shortCode)) {
                $shortCode = $this->generateShortCode();
            }
        }

        try {
            $sql = "INSERT INTO urls (original_url, short_code, custom_alias, user_id, expires_at) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $this->db->query($sql, [
                $originalUrl,
                $shortCode,
                $customAlias ?: null,
                $userId,
                $expiresAt
            ]);

            $url = $this->getUrlByCode($shortCode);
            
            $result['success'] = true;
            $result['data'] = $url->toApiArray();
            
        } catch (Exception $e) {
            $result['error'] = 'Failed to create short URL';
        }

        return $result;
    }

    public function getUrlByCode(string $shortCode): ?Url {
        $sql = "SELECT * FROM urls WHERE short_code = ? OR custom_alias = ?";
        $stmt = $this->db->query($sql, [$shortCode, $shortCode]);
        $row = $stmt->fetch();

        return $row ? new Url($row) : null;
    }

    public function getUrlById(int $id): ?Url {
        $sql = "SELECT * FROM urls WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $row = $stmt->fetch();

        return $row ? new Url($row) : null;
    }

    public function incrementClick(Url $url): bool {
        try {
            $sql = "UPDATE urls SET clicks = clicks + 1 WHERE id = ?";
            $this->db->query($sql, [$url->getId()]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function logClick(int $urlId, ?string $referer = null, ?string $userAgent = null, ?string $ipAddress = null): bool {
        try {
            $sql = "INSERT INTO click_logs (url_id, referer, user_agent, ip_address) 
                    VALUES (?, ?, ?, ?)";
            $this->db->query($sql, [$urlId, $referer, $userAgent, $ipAddress]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getStats(string $shortCode): array {
        $result = [
            'success' => false,
            'data' => null,
            'error' => null
        ];

        $url = $this->getUrlByCode($shortCode);
        
        if (!$url) {
            $result['error'] = 'URL not found';
            return $result;
        }

        $sql = "SELECT MAX(clicked_at) as last_clicked FROM click_logs WHERE url_id = ?";
        $stmt = $this->db->query($sql, [$url->getId()]);
        $log = $stmt->fetch();

        $result['success'] = true;
        $result['data'] = [
            'short_code' => $url->getShortCode(),
            'original_url' => $url->getOriginalUrl(),
            'short_url' => $url->getShortUrl(),
            'clicks' => $url->getClicks(),
            'created_at' => $url->getCreatedAt(),
            'last_clicked' => $log['last_clicked'] ?? null,
            'expires_at' => $url->getExpiresAt()
        ];

        return $result;
    }

    public function getAllUrls(?string $userId = null): array {
        if ($userId) {
            $sql = "SELECT * FROM urls WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->query($sql, [$userId]);
        } else {
            $sql = "SELECT * FROM urls ORDER BY created_at DESC LIMIT 100";
            $stmt = $this->db->query($sql);
        }

        $urls = [];
        while ($row = $stmt->fetch()) {
            $urls[] = (new Url($row))->toApiArray();
        }

        return $urls;
    }

    public function getAllUrlsPaginated(int $limit = 10, int $offset = 0): array {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$limit, $offset]);

        $urls = [];
        while ($row = $stmt->fetch()) {
            $urls[] = (new Url($row))->toApiArray();
        }

        return $urls;
    }

    public function getTotalUrls(string $search = ''): int {
        if (!empty($search)) {
            $sql = "SELECT COUNT(*) as total FROM urls WHERE short_code LIKE ? OR custom_alias LIKE ?";
            $stmt = $this->db->query($sql, ["%$search%", "%$search%"]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM urls";
            $stmt = $this->db->query($sql);
        }
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    public function searchUrls(string $search = '', int $limit = 10, int $offset = 0): array {
        if (!empty($search)) {
            $sql = "SELECT * FROM urls WHERE short_code LIKE ? OR custom_alias LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->query($sql, ["%$search%", "%$search%", $limit, $offset]);
        } else {
            $sql = "SELECT * FROM urls ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->query($sql, [$limit, $offset]);
        }

        $urls = [];
        while ($row = $stmt->fetch()) {
            $urls[] = (new Url($row))->toApiArray();
        }

        return $urls;
    }

    public function deleteUrl(int $id): bool {
        try {
            $sql = "DELETE FROM urls WHERE id = ?";
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function generateShortCode(): string {
        $code = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < SHORT_CODE_LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }
        return $code;
    }

    private function codeExists(string $shortCode): bool {
        $sql = "SELECT COUNT(*) as count FROM urls WHERE short_code = ? OR custom_alias = ?";
        $stmt = $this->db->query($sql, [$shortCode, $shortCode]);
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }

    private function aliasExists(string $alias): bool {
        $sql = "SELECT COUNT(*) as count FROM urls WHERE custom_alias = ?";
        $stmt = $this->db->query($sql, [$alias]);
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }

    private function isValidUrl(string $url): bool {
        $filtered = filter_var($url, FILTER_VALIDATE_URL);
        if ($filtered === false) {
            return false;
        }
        $parsed = parse_url($url);
        return isset($parsed['scheme']) && isset($parsed['host']) && 
               in_array($parsed['scheme'], ['http', 'https']);
    }

    private function isValidAlias(string $alias): bool {
        return preg_match('/^[a-zA-Z0-9-]{3,50}$/', $alias) === 1;
    }
}
