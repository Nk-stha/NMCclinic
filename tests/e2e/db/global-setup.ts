import { runSql, sqlFile } from './sql';

export default function globalSetup() {
  if (runSql("SHOW TABLES LIKE 'e2e_bak_%';").trim() !== '') {
    // An earlier run died before teardown; its backup holds the real content, so never overwrite it.
    throw new Error('e2e_bak_* tables exist from an interrupted run. Restore with tests/e2e/db/restore.sql first (see README).');
  }
  runSql(sqlFile('cleanup.sql') + sqlFile('snapshot.sql'));
  process.env.E2E_SNAPSHOT_TAKEN = '1';
}
