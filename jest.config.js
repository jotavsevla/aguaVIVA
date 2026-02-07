const nextJest = require("next/jest");

const createJestConfig = nextJest({
  dir: ".",
});

const jestConfig = createJestConfig({
  moduleDirectories: ["node_modules", "<rootDir>"],
  testEnvironment: "node",
  moduleNameMapper: {
    "^infra/(.*)$": "<rootDir>/infra/$1",
    "^models/(.*)$": "<rootDir>/models/$1",
    "^tests/(.*)$": "<rootDir>/tests/$1",
    "^pages/(.*)$": "<rootDir>/pages/$1",
  },
});

module.exports = jestConfig;
