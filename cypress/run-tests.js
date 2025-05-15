const cypress = require('cypress');

const specs = [
  'cypress/e2e/blocksUi/anon-cart.cy.js',
  'cypress/e2e/blocksUi/anon-checkout.cy.js',
  'cypress/e2e/classicUi/anon-cart.cy.js',
];

(async () => {
  for (const spec of specs) {
    console.log(`\n➡️ Exécution du test : ${spec}`);

    const result = await cypress.run({
      spec,
      headless: true,
      browser: 'chrome',
      config: {
        video: false
      }
    });
    
    if (result.totalFailed > 0) {
      console.error(`❌ Échec détecté dans : ${spec}`);
      process.exit(1);
    }
  }
  console.log('\n🎉 Tous les tests ont été exécutés avec succès.');
})();
