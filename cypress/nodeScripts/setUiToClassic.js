console.log('Setting UI to classic');
import runSqlDump from './runSqlDump.js';
import path from 'path';

async function update() {
  await runSqlDump(path.resolve('./cypress/fixtures/db/set-classic-ui.sql'));
}

update();