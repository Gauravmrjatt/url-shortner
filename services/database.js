const { MongoClient } = require('mongodb');

class Database {
  constructor() {
    this.client = null;
    this.db = null;
  }

  async connect() {
    const uri = process.env.MONGODB_URI || 'mongodb://localhost:27017';
    this.client = new MongoClient(uri);
    await this.client.connect();
    this.db = this.client.db();
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
