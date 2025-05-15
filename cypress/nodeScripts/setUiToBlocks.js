console.log('Setting UI to blocks');
import runSqlDump from './runSqlDump.js';
import getConnection from './dbConnection.js';

async function update() {
  await runSqlDump(await getConnection(), './cypress/fixtures/db/set-blocks-ui.sql');
}

update();