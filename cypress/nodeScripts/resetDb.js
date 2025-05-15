// cypress/nodeScripts/resetDb.js
import runSqlDump from './runSqlDump.js';
import getConnection from './dbConnection.js';

console.log('Resetting database to initial state');

async function update() {
    let dbConnection = await getConnection();
    await dbConnection.query('SET FOREIGN_KEY_CHECKS = 0;');
    await dbConnection.query(`DROP DATABASE IF EXISTS \`${process.env.DB_NAME}\`;`);
    await dbConnection.query(`CREATE DATABASE \`${process.env.DB_NAME}\`;`);
    await dbConnection.query('SET FOREIGN_KEY_CHECKS = 1;');
    await dbConnection.end();
    
    dbConnection = await getConnection();
    await runSqlDump(dbConnection, '../../../tests/dump.sql');
    await dbConnection.end();
    process.exit(0);
}

await update();