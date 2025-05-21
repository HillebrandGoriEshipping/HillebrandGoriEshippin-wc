const fs = require("fs-extra");
const path = require("path");
const { marked } = require("marked");

const sourceDir = "./docs/sources/guide"; // Dossier contenant les .md
const outputDir = "./docs/guide";     // Dossier de sortie pour les .html

function convertMarkdownFiles(src, dest) {
  fs.readdirSync(src, { withFileTypes: true }).forEach(entry => {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name.replace(/\.md$/, ".html"));

    if (entry.isDirectory()) {
      convertMarkdownFiles(srcPath, path.join(dest, entry.name));
    } else if (entry.isFile() && entry.name.endsWith(".md")) {
      const mdContent = fs.readFileSync(srcPath, "utf8");
      let htmlContent = marked(mdContent);
      htmlContent = htmlContent.replace(/\.md/, ".html");
      fs.ensureDirSync(path.dirname(destPath));
      fs.writeFileSync(destPath, htmlContent);
      console.log(`Converted: ${srcPath} → ${destPath}`);
    }
  });
}

convertMarkdownFiles(sourceDir, outputDir);