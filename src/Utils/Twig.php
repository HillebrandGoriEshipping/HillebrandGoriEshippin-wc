<?php

namespace HGeS\Utils;

use HGeS\Utils\Enums\GlobalEnum;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;
use Twig\Markup;

class Twig
{
    private static $twig;

    public static function getTwig()
    {
        if (!self::$twig) {
            $loader = new FilesystemLoader(HGES_PLUGIN_DIR . '/templates');
            self::$twig = new Environment($loader, [
                'cache' => false,
                'debug' => $_ENV['WP_DEBUG'] ?? false,
            ]);
            self::customFunctions();
        }
        return self::$twig;
    }

    public static function customFunctions()
    {
        //Add custom functions
        self::$twig->addFunction(new \Twig\TwigFunction('function', function ($name, ...$args) {
            return call_user_func_array("\\" . $name, $args);
        }));

        // Custom translation function
        self::$twig->addFunction(new \Twig\TwigFunction('translate', function ($text, $vars = []) {
            return Translator::translate($text, $vars);
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('carrierChecked', function ($carrierName) {
            $carrierChecked = '';
            $carrier = get_option('HGES_PREF_TRANSP', []);
            if (is_array($carrier) && in_array($carrierName, $carrier)) {
                $carrierChecked = 'checked';
            }

            return $carrierChecked;
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('workingDayChecked', function ($dayValue) {
            $workingDayChecked = '';
            $day = get_option('HGES_WORKING_DAYS', []);
            if (is_array($day) && in_array($dayValue, $day)) {
                $workingDayChecked = 'checked';
            }

            return $workingDayChecked;
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('checkboxChecked', function ($optionName, $expectedValue, $defaultValue = '') {
            $optionChecked = '';
            $optionContent = get_option($optionName);
            if (is_array($optionContent)) {
                $optionContent = array_map('stripcslashes', $optionContent);

                if (in_array($expectedValue, $optionContent)) {
                    $optionChecked = true;
                }
            } elseif ($optionContent === $expectedValue || (empty($optionContent) && $expectedValue == $defaultValue)) {
                $optionChecked = true;
            }

            return $optionChecked ? 'checked' : '';
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('optionSelected', function ($optionName, $expectedValue, $defaultValue = '') {
            $optionSelected = '';

            if (get_option($optionName) == $expectedValue) {
                $optionSelected = 'selected';
            } elseif (empty(get_option($optionName)) && $expectedValue == $defaultValue) {
                $optionSelected = 'selected';
            }

            return $optionSelected;
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('hgesOptionName', function ($optionName) {
            return OptionEnum::{$optionName};
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('hgesProductMeta', function ($metaName) {
            return ProductMetaEnum::{$metaName};
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('spawnComponent', function ($componentName, $props) {
            $props = json_encode($props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $html = "
                <div id=\"react-component-$componentName\"></div>
                <script type=\"module\">
                spawnComponent('$componentName', $props);
                </script>
            ";
            return new Markup($html, 'UTF-8');
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('getMessage', function ($messageKey, $var = []) {
            return Messages::getMessage($messageKey, $var);
        }));
    }
}
