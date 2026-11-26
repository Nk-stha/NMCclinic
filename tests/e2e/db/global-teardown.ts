import { runSql, sqlFile } from './sql';

const BACKUPS = ['e2e_bak_admin', 'e2e_bak_page_sections', 'e2e_bak_section_items'];

export default function globalTeardown() {
  // Only restore a snapshot this run took.
  if (process.env.E2E_SNAPSHOT_TAKEN !== '1') return;
  runSql(sqlFile('cleanup.sql'));
  const found = runSql("SHOW TABLES LIKE 'e2e_bak_%';").split('\n').slice(1).filter(Boolean).sort();
  if (found.join() !== BACKUPS.join()) {
    // Never run restore.sql's DELETE without a complete backup to put back.
    throw new Error(`Backup set incomplete (found: ${found.join(', ') || 'none'}); page_sections NOT restored. Inspect the e2e_bak_* tables by hand.`);
  }
  runSql(sqlFile('restore.sql'));
}
