console.log('Setting UI to blocks');
import runSqlDump from './runSqlDump.js';
import getConnection from './dbConnection.js';
import path from 'path';

async function update() {
  await runSqlDump(path.resolve('./cypress/fixtures/db/set-blocks-ui.sql'));
}

update();