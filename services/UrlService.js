const database = require('./database');

const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

function generateShortCode(length) {
  let code = '';
  for (let i = 0; i < length; i++) {
    code += ALPHABET[Math.floor(Math.random() * ALPHABET.length)];
  }
  return code;
}

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
    return generateShortCode(this.codeLength);
  }

  async codeExists(shortCode) {
    const collection = await database.getCollection('urls');
    const count = await collection.countDocuments({
      $or: [{ short_code: shortCode }, { custom_alias: shortCode }]
    });
    return count > 0;
  }

  async aliasExists(alias) {
    const collection = await database.getCollection('urls');
    const count = await collection.countDocuments({ custom_alias: alias });
    return count > 0;
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
      const collection = await database.getCollection('urls');
      const urlDoc = {
        original_url: originalUrl,
        short_code: shortCode,
        custom_alias: customAlias || null,
        clicks: 0,
        created_at: new Date(),
        expires_at: expiresAt ? new Date(expiresAt) : null,
        is_active: true,
        user_id: userId || null
      };
      
      await collection.insertOne(urlDoc);
      
      result.success = true;
      result.data = this.toApiArray(urlDoc);
    } catch (error) {
      result.error = 'Failed to create short URL';
    }

    return result;
  }

  async getUrlByCode(shortCode) {
    const collection = await database.getCollection('urls');
    const url = await collection.findOne({
      $or: [{ short_code: shortCode }, { custom_alias: shortCode }]
    });
    return url ? this.toApiArray(url) : null;
  }

  async getUrlById(id) {
    const collection = await database.getCollection('urls');
    const { ObjectId } = require('mongodb');
    const url = await collection.findOne({ _id: new ObjectId(id) });
    return url ? this.toApiArray(url) : null;
  }

  async incrementClick(urlId) {
    try {
      const collection = await database.getCollection('urls');
      const { ObjectId } = require('mongodb');
      await collection.updateOne(
        { _id: new ObjectId(urlId) },
        { $inc: { clicks: 1 } }
      );
      return true;
    } catch {
      return false;
    }
  }

  async logClick(urlId, referer = null, userAgent = null, ipAddress = null) {
    try {
      const collection = await database.getCollection('click_logs');
      const { ObjectId } = require('mongodb');
      await collection.insertOne({
        url_id: new ObjectId(urlId),
        clicked_at: new Date(),
        referer,
        user_agent: userAgent,
        ip_address: ipAddress
      });
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

    const logsCollection = await database.getCollection('click_logs');
    const { ObjectId } = require('mongodb');
    const lastClick = await logsCollection.findOne(
      { url_id: new ObjectId(url.id) },
      { sort: { clicked_at: -1 } }
    );

    result.success = true;
    result.data = {
      short_code: url.short_code,
      original_url: url.original_url,
      short_url: url.short_url,
      clicks: url.clicks,
      created_at: url.created_at,
      last_clicked: lastClick ? lastClick.clicked_at : null,
      expires_at: url.expires_at
    };

    return result;
  }

  async getAllUrls(userId = null) {
    const collection = await database.getCollection('urls');
    let query = {};
    if (userId) {
      query.user_id = userId;
    }
    const cursor = collection.find(query).sort({ created_at: -1 }).limit(100);
    const urls = await cursor.toArray();
    return urls.map(url => this.toApiArray(url));
  }

  async searchUrls(search = '', limit = 10, offset = 0) {
    const collection = await database.getCollection('urls');
    let query = {};
    if (search) {
      query.$or = [
        { short_code: { $regex: search, $options: 'i' } },
        { custom_alias: { $regex: search, $options: 'i' } }
      ];
    }
    const cursor = collection.find(query).sort({ created_at: -1 }).skip(offset).limit(limit);
    const urls = await cursor.toArray();
    return urls.map(url => this.toApiArray(url));
  }

  async getTotalUrls(search = '') {
    const collection = await database.getCollection('urls');
    let query = {};
    if (search) {
      query.$or = [
        { short_code: { $regex: search, $options: 'i' } },
        { custom_alias: { $regex: search, $options: 'i' } }
      ];
    }
    return await collection.countDocuments(query);
  }

  async deleteUrl(id) {
    try {
      const collection = await database.getCollection('urls');
      const { ObjectId } = require('mongodb');
      await collection.deleteOne({ _id: new ObjectId(id) });
      return true;
    } catch {
      return false;
    }
  }

  toApiArray(url) {
    return {
      id: url._id ? url._id.toString() : url.id,
      original_url: url.original_url,
      short_code: url.short_code,
      custom_alias: url.custom_alias,
      short_url: `${this.baseUrl}/s/${url.short_code}`,
      clicks: url.clicks,
      created_at: url.created_at,
      expires_at: url.expires_at,
      is_active: url.is_active
    };
  }
}

module.exports = UrlService;
