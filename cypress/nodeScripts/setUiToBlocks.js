console.log('Setting UI to blocks');
const runSqlDump = require('./runSqlDump');
const getConnection = require('./dbConnection');

async function update() {
  await runSqlDump(await getConnection(), './cypress/fixtures/db/set-blocks-ui.sql');
}

update();