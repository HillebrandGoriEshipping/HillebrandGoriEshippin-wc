console.log('Setting UI to blocks');
const mysql = require('mysql2/promise');
const runSqlDump = require('./runSqlDump');

async function update() {
  const connection = await mysql.createConnection({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
  });

  await runSqlDump(connection, './cypress/fixtures/db/set-blocks-ui.sql');
}

update();