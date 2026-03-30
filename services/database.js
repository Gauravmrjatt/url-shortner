const mysql = require('mysql2/promise');

class Database {
  constructor() {
    this.pool = null;
  }

  async getPool() {
    if (!this.pool) {
      this.pool = mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASS || '',
        database: process.env.DB_NAME || 'url_shortener',
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0
      });
    }
    return this.pool;
  }

  async query(sql, params = []) {
    const pool = await this.getPool();
    const [rows] = await pool.execute(sql, params);
    return rows;
  }

  async close() {
    if (this.pool) {
      await this.pool.end();
    }
  }
}

module.exports = new Database();
