console.log('Setting UI to classic');
import runSqlDump from './runSqlDump.js';
import path from 'path';

export default async function update() {
  await runSqlDump(path.resolve('../../../tests/set_classic_ui.sql'));
}
