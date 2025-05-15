console.log('Setting UI to classic');
import runSqlDump from './runSqlDump.js';
import getConnection from './dbConnection.js';

async function update() {
  await runSqlDump(await getConnection(), './cypress/fixtures/db/set-classic-ui.sql');
}

update();