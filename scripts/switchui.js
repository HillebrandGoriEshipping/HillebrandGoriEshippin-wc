import setUiToClassic from './setUiToClassic.js';
import setUiToBlocks from './setUiToBlocks.js';
import path from 'path';
import fs from 'fs';
import dotenv from 'dotenv';

const envPaths = [
  path.resolve('../../../.env'),
  path.resolve('../../../.env.e2e'),
];
const envFiles = envPaths.filter((file) => fs.existsSync(file));
dotenv.config({
  override: true,
  path: envFiles 
});

const uiMode = process.argv[2] || 'blocks';

console.log(`Switching UI mode to: ${uiMode}`);

if (uiMode === 'blocks') {
    try {
        await setUiToBlocks();
        console.log('UI switched to blocks'); 
    } catch (err) {
        console.error('Error switching UI to blocks:', err);
        process.exit(1);
    }
} else if (uiMode === 'classic') {
    try {
        await setUiToClassic();
        console.log('UI switched to classic');
    } catch (err) {
        console.error('Error switching UI to classic:', err);
        process.exit(1);
    }
}