import cypress from 'cypress';
import { readdirSync } from 'fs';
import { join } from 'path';

const specs = [];
// get recursive list of all .cy.js files path in the cypress/e2e directory
const e2eDir = join('cypress', 'e2e');
const getSpecFiles = (dir) => {
  const files = readdirSync(dir, { withFileTypes: true });
  for (const file of files) {
    if (file.isDirectory()) {
      getSpecFiles(join(dir, file.name));
    } else if (file.name.endsWith('.cy.js')) {
      specs.push(join(dir, file.name));
    }
  }
};
getSpecFiles(e2eDir);


(async () => {
  try {
    for (const spec of specs) {
      console.log(`\n➡️ Executing test: ${spec}`);

      const result = await cypress.run({
        spec,
        headless: true,
        browser: 'chrome',
        config: {
          video: false
        }
      });

      if (result.totalFailed > 0) {
        console.error(`❌ Failure in: ${spec}`);
        process.exit(1);
      }
    }

    console.log('\n🎉 Tous les tests ont été exécutés avec succès.');
  } catch (err) {
    console.error(`❌ Error in Cypress tests:\n`, err);
    process.exit(1);
  }
})();