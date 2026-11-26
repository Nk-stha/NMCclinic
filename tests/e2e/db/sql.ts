import { execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import path from 'node:path';

const repoRoot = path.resolve(__dirname, '../../..');
const mysql = `docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'`;

export const sqlFile = (name: string) => readFileSync(path.join(__dirname, name), 'utf8');

/** Pipes SQL to the dev DB. Override the command with E2E_DB_EXEC (it reads SQL on stdin, runs from the repo root). */
export function runSql(sql: string): string {
  const run = (cmd: string) =>
    execSync(cmd, { cwd: repoRoot, input: sql, encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'] });
  if (process.env.E2E_DB_EXEC) return run(process.env.E2E_DB_EXEC);
  try {
    return run(mysql);
  } catch (error) {
    // The user is in the docker group but this login shell is not yet: retry through `sg docker`.
    if (!/permission denied/i.test(String((error as { stderr?: string }).stderr))) throw error;
    return run(`sg docker -c "${mysql.replace(/["$`\\]/g, '\\$&')}"`);
  }
}
