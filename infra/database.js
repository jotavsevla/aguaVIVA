import { Pool } from "pg";

let pool;

function getSSLConfig() {
  if (process.env.NODE_ENV === "production") {
    return {
      ssl: {
        rejectUnauthorized: false,
      },
    };
  }
  return {};
}

function createPool() {
  const isTestEnvironment = process.env.NODE_ENV === "test";

  const config = {
    host: isTestEnvironment
      ? process.env.POSTGRES_HOST_TEST
      : process.env.POSTGRES_HOST,
    port: isTestEnvironment
      ? process.env.POSTGRES_PORT_TEST
      : process.env.POSTGRES_PORT,
    user: process.env.POSTGRES_USER,
    password: process.env.POSTGRES_PASSWORD,
    database: isTestEnvironment
      ? process.env.POSTGRES_DB_TEST
      : process.env.POSTGRES_DB,
    max: 20,
    idleTimeoutMillis: 30000,
    connectionTimeoutMillis: 2000,
    ...getSSLConfig(),
  };

  return new Pool(config);
}

function getPool() {
  if (!pool) {
    pool = createPool();
  }
  return pool;
}

async function query(queryObject) {
  const currentPool = getPool();
  return currentPool.query(queryObject);
}

async function getClient() {
  const currentPool = getPool();
  return currentPool.connect();
}

async function end() {
  if (pool) {
    await pool.end();
    pool = null;
  }
}

const database = {
  query,
  getClient,
  end,
};

export default database;
