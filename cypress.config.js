const { defineConfig } = require("cypress");
const dotenv = require("dotenv");
dotenv.config({ path: ['../../../.env.e2e'] });

module.exports = defineConfig({
  e2e: {
    defaultCommandTimeout: 10000,
    baseUrl: 'http://localhost:8888',
    setupNodeEvents(on, config) {
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
