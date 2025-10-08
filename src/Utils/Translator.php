<?php

namespace HGeS\Utils;

class Translator
{
    /**
     * Stores translation key-value pairs for use within the Translator utility.
     *
     * @var array
     */
    private static $translations = [];

    /**
     * Stores the currently loaded locale.
     *
     * @var string|null $loadedLocale The locale that has been loaded, or null if none is loaded.
     */
    private static $loadedLocale = null;

    /**
     * Loads translation strings for the specified locale from a CSV file.
     *
     * If no locale is provided, the current WordPress locale is used.
     * The method reads translation key-value pairs from a CSV file located in the plugin's i18n directory.
     * Each row in the CSV should contain a key and its corresponding translation, separated by a semicolon.
     * The loaded translations are stored in the static $translations property.
     * If the translations for the requested locale are already loaded, the method returns early.
     *
     * @param string|null $locale Optional. The locale to load translations for. Defaults to current WordPress locale.
     * @return void
     */
    public static function loadTranslations(?string $locale = null): void
    {
        if (!$locale) {
            $locale = get_locale();
        }

        if (self::$loadedLocale === $locale) {
            return;
        }

        $baseDir = HGES_PLUGIN_DIR . '/i18n/';
        $langFile = $baseDir . 'messages.' . $locale . '.csv';
        error_log($langFile);

        $keys = [];
        $translations = [];
        if (file_exists($langFile)) {
            if (($handle = fopen($langFile, 'r')) !== false) {
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    $keys[] = $row[0];
                    $translations[$row[0]] = $row[1];
                }
                fclose($handle);
            }
        }

        foreach ($keys as $key) {
            self::$translations[$key] = $translations[$key] ?? $key;
        }

        self::$loadedLocale = $locale;
    }

    /**
     * Translates a given key using loaded translations and replaces variables in the translation string.
     *
     * Loads the translations if not already loaded, retrieves the translation for the specified key,
     * and replaces any placeholders (e.g., {name}) in the translation string with the provided values.
     *
     * @param string $key The translation key to look up.
     * @param array $vars Associative array of variables to replace in the translation string.
     *                    Example: ['name' => 'John']
     * @return string The translated string with variables replaced, or the key itself if no translation is found.
     */
    public static function translate(string $key, array $vars = []): string
    {
        self::loadTranslations();

        $translation = self::$translations[$key] ?? $key;

        foreach ($vars as $var => $value) {
            $translation = str_replace('{' . $var . '}', $value, $translation);
        }

        return $translation;
    }
}
