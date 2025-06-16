console.log('Setting UI to blocks');
import runSqlDump from './runSqlDump.js';
import path from 'path';

export default async function update() {
  await runSqlDump(path.resolve('../../../tests/set_blocks_ui.sql'));
}