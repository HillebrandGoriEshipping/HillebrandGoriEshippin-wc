import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import path from 'path';
import fs from 'fs';

const envPaths = [
  path.resolve('../../../.env'),
  path.resolve('../../../.env.e2e'),
];
const envFiles = envPaths.filter((file) => fs.existsSync(file));
dotenv.config({
  override: true,
  path: envFiles 
});

export default () => {
    console.log('Connecting to the database with', {
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USER,
        database: process.env.DB_NAME
    });
    return mysql.createConnection({
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });
} 