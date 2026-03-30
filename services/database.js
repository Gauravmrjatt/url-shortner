const { MongoClient } = require('mongodb');

class Database {
  constructor() {
    this.client = null;
    this.db = null;
  }

  async connect() {
    const uri = `mongodb://${process.env.DB_USER}:${process.env.DB_PASS}@${process.env.DB_HOST}:${process.env.DB_PORT || 27017}`;
    this.client = new MongoClient(uri);
    await this.client.connect();
    this.db = this.client.db(process.env.DB_NAME || 'urlshortner');
    return this.db;
  }

  async getDb() {
    if (!this.db) {
      await this.connect();
    }
    return this.db;
  }

  async getCollection(name) {
    const db = await this.getDb();
    return db.collection(name);
  }

  async close() {
    if (this.client) {
      await this.client.close();
    }
  }
}

module.exports = new Database();
