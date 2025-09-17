#!/bin/bash

# Usage: ./make_zip.sh liste.txt sortie.zip

LIST_FILE="$1"
ZIP_FILE="$2"

if [ -z "$LIST_FILE" ] || [ -z "$ZIP_FILE" ]; then
  echo "Usage: $0 <list_file> <output_zip>"
  exit 1
fi

# Vérifie que le fichier de liste existe
if [ ! -f "$LIST_FILE" ]; then
  echo "Le fichier de liste '$LIST_FILE' est introuvable."
  exit 1
fi

# Supprime l'archive existante si elle existe
if [ -f "$ZIP_FILE" ]; then
  rm "$ZIP_FILE"
fi

rm -rf vendor node_modules
npm install --omit=dev
composer install --no-dev --optimize-autoloader

# On lit la liste et on zippe chaque entrée
zip -r "$ZIP_FILE" -@ < "$LIST_FILE"

echo "Archive créée : $ZIP_FILE"

npm install && composer install
