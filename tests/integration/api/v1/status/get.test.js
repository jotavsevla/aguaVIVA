import orchestrator from "tests/orchestrator.js";

beforeAll(async () => {
  await orchestrator.waitForDatabase();
  await orchestrator.clearDatabase();
  await orchestrator.runMigrations();
});

describe("GET /api/v1/status", () => {
  test("should return 200 with database status", async () => {
    const response = await fetch("http://localhost:3000/api/v1/status");
    const body = await response.json();

    expect(response.status).toBe(200);
    expect(body).toHaveProperty("updated_at");
    expect(body).toHaveProperty("dependencies");
    expect(body.dependencies).toHaveProperty("database");
    expect(body.dependencies.database).toHaveProperty("version");
    expect(body.dependencies.database).toHaveProperty("max_connections");
    expect(body.dependencies.database).toHaveProperty("opened_connections");

    const parsedUpdatedAt = new Date(body.updated_at).toISOString();
    expect(body.updated_at).toBe(parsedUpdatedAt);

    expect(typeof body.dependencies.database.version).toBe("string");
    expect(typeof body.dependencies.database.max_connections).toBe("number");
    expect(typeof body.dependencies.database.opened_connections).toBe("number");
    expect(body.dependencies.database.opened_connections).toBeGreaterThanOrEqual(
      1,
    );
    expect(body.dependencies.database.max_connections).toBeGreaterThan(0);
  });
});
