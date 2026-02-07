import database from "infra/database.js";

async function clearDatabase() {
  await database.query({
    text: `
      DROP SCHEMA public CASCADE;
      CREATE SCHEMA public;
    `,
  });
}

async function runMigrations() {
  const { exec } = await import("child_process");
  const { promisify } = await import("util");
  const execAsync = promisify(exec);

  await execAsync("npm run migrate:up");
}

async function waitForDatabase(maxAttempts = 10) {
  let currentAttempt = 1;

  while (currentAttempt <= maxAttempts) {
    try {
      await database.query({ text: "SELECT 1" });
      return;
    } catch (error) {
      currentAttempt++;
      if (currentAttempt > maxAttempts) {
        throw new Error(
          `Database not ready after ${maxAttempts} attempts: ${error.message}`,
        );
      }
      await new Promise((resolve) => setTimeout(resolve, 1000));
    }
  }
}

const orchestrator = {
  clearDatabase,
  runMigrations,
  waitForDatabase,
};

export default orchestrator;
