const { defineConfig } = require("cypress");
const dotenv = require("dotenv");
const path = require('path');
const fs = require('fs');

const envPaths = [
  path.resolve('../../../.env'),
  path.resolve('../../../.env.e2e'),
];
const envFiles = envPaths.filter((file) => fs.existsSync(file));
dotenv.config({
  override: true,
  path: envFiles 
});

module.exports = defineConfig({
  e2e: {
    defaultCommandTimeout: 10000,
    baseUrl: process.env.WP_HOME,
    setupNodeEvents(on, config) {

      on('before:spec', (spec) => {
        const { execSync } = require('child_process');
        const output = execSync('node ./cypress/nodeScripts/resetDb.js --input-type=module').toString();
        console.log(output);
      })


       on('task', {
        setUiToBlocks() {
          const { execSync } = require('child_process');
          const output = execSync('node ./cypress/nodeScripts/setUiToBlocks.js').toString();
          console.log(output);
          return output;
        },
        setUiToClassic() {
          const { execSync } = require('child_process');
          const output = execSync('node ./cypress/nodeScripts/setUiToClassic.js').toString();
          return output;
        }
       });
    },
  },
});
