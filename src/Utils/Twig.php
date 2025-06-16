<?php

namespace HGeS\Utils;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;

class Twig
{
    private static $twig;

    public static function getTwig()
    {
        if (!self::$twig) {
            $loader = new FilesystemLoader(HGES_PLUGIN_DIR . '/templates');
            self::$twig = new Environment($loader, [
                'cache' => false,
                'debug' => $_ENV['WP_DEBUG'] ?? true,
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
        self::$twig->addFunction(new \Twig\TwigFunction('__', function ($text, $domain = 'hges') {
            return __($text, $domain);
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
    }
}
