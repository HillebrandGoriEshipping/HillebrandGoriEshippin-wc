import { execSync } from 'child_process';

/**
 * Executes a SQL dump file against a MySQL database.
 * @param {import('mysql2/promise').Connection} connection - Connection to the MySQL database
 * @param {string} filePath - Path to the SQL dump file to execute
 */
export default async (filePath) => {

  const {
    DB_HOST,
    DB_PORT,
    DB_NAME,
    DB_USER,
    DB_PASSWORD
  } = process.env;

  try {
    const command = `mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < "${filePath}"`;
    console.log(`🔄 Exécution du dump SQL : ${filePath}`);
    execSync(command, { stdio: 'inherit', shell: true });
    console.log('✅ Dump importé avec succès.');
  } catch (error) {
    console.error('❌ Échec de l\'import du dump SQL :', error.message);
    process.exit(1);
  }
}