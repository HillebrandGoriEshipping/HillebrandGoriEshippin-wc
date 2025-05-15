console.log('Resetting database to initial state');
const runSqlDump = require('./runSqlDump');
const getConnection = require('./dbConnection');

async function update() {
    // empty the database
    await.getConnection().query('SET FOREIGN_KEY_CHECKS = 0;');
    await getConnection().query('DROP DATABASE IF EXISTS `' + process.env.DB_NAME + '`;');
    await getConnection().query('CREATE DATABASE `' + process.env.DB_NAME + '`;');
    await getConnection().query('SET FOREIGN_KEY_CHECKS = 1;');
    // run the SQL dump
    await runSqlDump(await getConnection(), './tests/dump.sql');
}

update();