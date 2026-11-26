import { runSql, sqlFile } from './sql';

export default function globalTeardown() {
  // Only restore a snapshot this run took; otherwise restore.sql would wipe content with nothing to put back.
  if (process.env.E2E_SNAPSHOT_TAKEN !== '1') return;
  runSql(sqlFile('cleanup.sql') + sqlFile('restore.sql'));
}
