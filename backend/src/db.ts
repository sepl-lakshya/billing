import mysql from 'mysql2/promise';
import { config } from './config';

export const pool = mysql.createPool({
  host: config.db.host,
  port: config.db.port,
  user: config.db.user,
  password: config.db.password,
  database: config.db.database,
  waitForConnections: true,
  connectionLimit: config.db.connectionLimit,
  queueLimit: 0,
  dateStrings: true,
  namedPlaceholders: false,
});

/** Run a query and return the rows (typed loosely as any[]). */
export async function query<T = any>(sql: string, params: any[] = []): Promise<T[]> {
  const [rows] = await pool.query(sql, params);
  return rows as T[];
}

/** Run an INSERT/UPDATE/DELETE and return the raw ResultSetHeader. */
export async function execute(sql: string, params: any[] = []): Promise<mysql.ResultSetHeader> {
  const [result] = await pool.execute(sql, params);
  return result as mysql.ResultSetHeader;
}

/** Fetch a single row or undefined. */
export async function queryOne<T = any>(sql: string, params: any[] = []): Promise<T | undefined> {
  const rows = await query<T>(sql, params);
  return rows[0];
}

/** Generate a random hash guaranteed unique across the given table(s). */
export async function generateUniqueHash(tables: string[], length = 64): Promise<string> {
  const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  // eslint-disable-next-line no-constant-condition
  while (true) {
    let hash = '';
    for (let i = 0; i < length; i++) hash += chars.charAt(Math.floor(Math.random() * chars.length));
    let exists = false;
    for (const t of tables) {
      const rows = await query(`SELECT id FROM ${t} WHERE hash = ? LIMIT 1`, [hash]);
      if (rows.length > 0) {
        exists = true;
        break;
      }
    }
    if (!exists) return hash;
  }
}

/** Run work inside a transaction, auto commit/rollback. */
export async function withTransaction<T>(
  work: (conn: mysql.PoolConnection) => Promise<T>
): Promise<T> {
  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    const result = await work(conn);
    await conn.commit();
    return result;
  } catch (err) {
    await conn.rollback();
    throw err;
  } finally {
    conn.release();
  }
}
