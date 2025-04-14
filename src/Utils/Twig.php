<?php

namespace HGeS\Utils;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static $twig;

    public static function getTwig()
    {
        if (!self::$twig) {
            $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
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
            $carrier = get_option('VINW_PREF_TRANSP', []);
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

        return self::$twig;
    }
}
