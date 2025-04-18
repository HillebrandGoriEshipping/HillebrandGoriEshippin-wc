<?php

namespace HGeS\Utils;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use HGeS\Utils\Enums\OptionEnum;

class Twig
{
    private static $twig;

    public static function getTwig()
    {
        if (!self::$twig) {
            $loader = new FilesystemLoader(HGeS_PLUGIN_DIR . '/templates');
            self::$twig = new Environment($loader, [
                'cache' => false,
            ]);
        }

        //Add custom functions
        self::$twig->addFunction(new \Twig\TwigFunction('function', function ($name, ...$args) {
            return call_user_func_array("\\" . $name, $args);
        }));

        self::$twig->addFunction(new \Twig\TwigFunction('carrierChecked', function ($carrierName) {
            $carrierChecked = '';
            $carrier = get_option('HGES_PREF_TRANSP', []);
            if (is_array($carrier) && in_array($carrierName, $carrier)) {
                $carrierChecked = 'checked';
            }

            return $carrierChecked;
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

        return self::$twig;
    }
}
