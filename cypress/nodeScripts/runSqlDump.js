const fs = require('fs');

/**
 * Executes a SQL dump file against a MySQL database.
 * @param {import('mysql2/promise').Connection} connection - Connection to the MySQL database
 * @param {string} filePath - Path to the SQL dump file to execute
 */
module.exports = async (connection, filePath) => {
    try {
    const sql = fs.readFileSync(filePath, 'utf8');

    const statements = sql
      .split(/;\s*$/m) 
      .map(s => s.trim())
      .filter(s => s.length > 0 && !s.startsWith('--'));

    for (const statement of statements) {
      await connection.query(statement);
    }

    console.log('Dump SQL exécuté avec succès');
  } catch (err) {
    console.error('Erreur lors de l\'exécution du dump :', err);
  } finally {
    await connection.end();
  }
}