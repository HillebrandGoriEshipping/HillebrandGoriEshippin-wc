import { defineConfig } from "cypress";
import dotenv from "dotenv";
import path from "path";
import fs from "fs";
import { execSync } from 'child_process';

const envPaths = [
  path.resolve('../../../.env'),
  path.resolve('../../../.env.e2e'),
];
const envFiles = envPaths.filter((file) => fs.existsSync(file));
dotenv.config({
  override: true,
  path: envFiles 
});

export default defineConfig({
  e2e: {
    defaultCommandTimeout: 15000,
    baseUrl: process.env.WP_HOME,
    setupNodeEvents(on, config) {

      on('before:spec', (spec) => {
        const output = execSync('node ./scripts/resetDb.js --input-type=module').toString();
        console.log(output);
      });

      on('task', {
        setUiToBlocks() {
          const output = execSync('node ./scripts/setUiToBlocks.js').toString();
          console.log(output);
          return output;
        },
        setUiToClassic() {
          const output = execSync('node ./scripts/setUiToClassic.js').toString();
          return output;
        }
      });
    },
  },
});
