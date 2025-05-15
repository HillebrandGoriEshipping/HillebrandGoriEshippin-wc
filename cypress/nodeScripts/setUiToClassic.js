console.log('Setting UI to classic');
const runSqlDump = require('./runSqlDump');
const getConnection = require('./dbConnection');

async function update() {
  await runSqlDump(await getConnection(), './cypress/fixtures/db/set-classic-ui.sql');
}

update();