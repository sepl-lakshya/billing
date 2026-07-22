/*
 * Creates the database, tables and seed data by executing db/schema.sql and
 * db/seed.sql. Run with:  npm run db:setup
 *
 * Reads connection settings from .env (same as the server).
 */
const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
require('dotenv').config();

async function run() {
  const host = process.env.DB_HOST || 'localhost';
  const port = parseInt(process.env.DB_PORT || '3306', 10);
  const user = process.env.DB_USER || 'root';
  const password = process.env.DB_PASSWORD || '';

  const schema = fs.readFileSync(path.join(__dirname, '..', 'db', 'schema.sql'), 'utf8');
  const seed = fs.readFileSync(path.join(__dirname, '..', 'db', 'seed.sql'), 'utf8');

  // multipleStatements lets us run the whole .sql files at once.
  const conn = await mysql.createConnection({
    host,
    port,
    user,
    password,
    multipleStatements: true,
  });

  console.log('> Applying schema.sql ...');
  await conn.query(schema);
  console.log('> Applying seed.sql ...');
  await conn.query(seed);

  console.log('Database setup complete.');
  await conn.end();
}

run().catch((err) => {
  console.error('Database setup failed:', err.message);
  process.exit(1);
});
