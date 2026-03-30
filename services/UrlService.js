const { nanoid } = require('nanoid');
const database = require('./database');

class UrlService {
  constructor() {
    this.baseUrl = process.env.BASE_URL || 'http://localhost:3000';
    this.codeLength = parseInt(process.env.SHORT_CODE_LENGTH) || 6;
  }

  isValidUrl(url) {
    try {
      const parsed = new URL(url);
      return ['http:', 'https:'].includes(parsed.protocol);
    } catch {
      return false;
    }
  }

  isValidAlias(alias) {
    return /^[a-zA-Z0-9-]{3,50}$/.test(alias);
  }

  generateShortCode() {
    return nanoid(this.codeLength);
  }

  async codeExists(shortCode) {
    const rows = await database.query(
      'SELECT COUNT(*) as count FROM urls WHERE short_code = ? OR custom_alias = ?',
      [shortCode, shortCode]
    );
    return rows[0].count > 0;
  }

  async aliasExists(alias) {
    const rows = await database.query(
      'SELECT COUNT(*) as count FROM urls WHERE custom_alias = ?',
      [alias]
    );
    return rows[0].count > 0;
  }

  async createShortUrl(originalUrl, customAlias = null, userId = null, expiresAt = null) {
    const result = { success: false, data: null, error: null };
    originalUrl = originalUrl.trim();

    if (!this.isValidUrl(originalUrl)) {
      result.error = 'Invalid URL format';
      return result;
    }

    let shortCode;
    if (customAlias) {
      customAlias = customAlias.trim();
      if (!this.isValidAlias(customAlias)) {
        result.error = 'Invalid alias format. Use only letters, numbers, and hyphens (3-50 characters)';
        return result;
      }
      if (await this.aliasExists(customAlias)) {
        result.error = 'This alias is already taken';
        return result;
      }
      shortCode = customAlias;
    } else {
      shortCode = this.generateShortCode();
      while (await this.codeExists(shortCode)) {
        shortCode = this.generateShortCode();
      }
    }

    try {
      const result2 = await database.query(
        'INSERT INTO urls (original_url, short_code, custom_alias, user_id, expires_at) VALUES (?, ?, ?, ?, ?)',
        [originalUrl, shortCode, customAlias || null, userId, expiresAt]
      );

      const url = await this.getUrlByCode(shortCode);
      result.success = true;
      result.data = url.toApiArray();
    } catch (error) {
      result.error = 'Failed to create short URL';
    }

    return result;
  }

  async getUrlByCode(shortCode) {
    const rows = await database.query(
      'SELECT * FROM urls WHERE short_code = ? OR custom_alias = ?',
      [shortCode, shortCode]
    );
    return rows.length > 0 ? new Url(rows[0], this.baseUrl) : null;
  }

  async getUrlById(id) {
    const rows = await database.query('SELECT * FROM urls WHERE id = ?', [id]);
    return rows.length > 0 ? new Url(rows[0], this.baseUrl) : null;
  }

  async incrementClick(urlId) {
    try {
      await database.query('UPDATE urls SET clicks = clicks + 1 WHERE id = ?', [urlId]);
      return true;
    } catch {
      return false;
    }
  }

  async logClick(urlId, referer = null, userAgent = null, ipAddress = null) {
    try {
      await database.query(
        'INSERT INTO click_logs (url_id, referer, user_agent, ip_address) VALUES (?, ?, ?, ?)',
        [urlId, referer, userAgent, ipAddress]
      );
      return true;
    } catch {
      return false;
    }
  }

  async getStats(shortCode) {
    const result = { success: false, data: null, error: null };

    const url = await this.getUrlByCode(shortCode);
    if (!url) {
      result.error = 'URL not found';
      return result;
    }

    const logs = await database.query(
      'SELECT MAX(clicked_at) as last_clicked FROM click_logs WHERE url_id = ?',
      [url.id]
    );

    result.success = true;
    result.data = {
      short_code: url.shortCode,
      original_url: url.originalUrl,
      short_url: url.shortUrl,
      clicks: url.clicks,
      created_at: url.createdAt,
      last_clicked: logs[0].last_clicked || null,
      expires_at: url.expiresAt
    };

    return result;
  }

  async getAllUrls(userId = null) {
    let rows;
    if (userId) {
      rows = await database.query('SELECT * FROM urls WHERE user_id = ? ORDER BY created_at DESC LIMIT 100', [userId]);
    } else {
      rows = await database.query('SELECT * FROM urls ORDER BY created_at DESC LIMIT 100');
    }
    return rows.map(row => new Url(row, this.baseUrl).toApiArray());
  }

  async searchUrls(search = '', limit = 10, offset = 0) {
    let rows;
    if (search) {
      rows = await database.query(
        'SELECT * FROM urls WHERE short_code LIKE ? OR custom_alias LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
        [`%${search}%`, `%${search}%`, limit, offset]
      );
    } else {
      rows = await database.query(
        'SELECT * FROM urls ORDER BY created_at DESC LIMIT ? OFFSET ?',
        [limit, offset]
      );
    }
    return rows.map(row => new Url(row, this.baseUrl).toApiArray());
  }

  async getTotalUrls(search = '') {
    let rows;
    if (search) {
      rows = await database.query(
        'SELECT COUNT(*) as total FROM urls WHERE short_code LIKE ? OR custom_alias LIKE ?',
        [`%${search}%`, `%${search}%`]
      );
    } else {
      rows = await database.query('SELECT COUNT(*) as total FROM urls');
    }
    return rows[0].total;
  }

  async deleteUrl(id) {
    try {
      await database.query('DELETE FROM urls WHERE id = ?', [id]);
      return true;
    } catch {
      return false;
    }
  }
}

class Url {
  constructor(row, baseUrl) {
    this.id = row.id;
    this.originalUrl = row.original_url;
    this.shortCode = row.short_code;
    this.customAlias = row.custom_alias;
    this.clicks = row.clicks;
    this.createdAt = row.created_at;
    this.expiresAt = row.expires_at;
    this.isActive = row.is_active;
    this.userId = row.user_id;
    this.baseUrl = baseUrl;
  }

  getShortUrl() {
    return `${this.baseUrl}/s/${this.shortCode}`;
  }

  toApiArray() {
    return {
      id: this.id,
      original_url: this.originalUrl,
      short_code: this.shortCode,
      custom_alias: this.customAlias,
      short_url: this.getShortUrl(),
      clicks: this.clicks,
      created_at: this.createdAt,
      expires_at: this.expiresAt,
      is_active: this.isActive
    };
  }
}

module.exports = UrlService;
