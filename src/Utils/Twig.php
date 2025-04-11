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
                'cache' => false, // Vous pouvez activer le cache en production
            ]);
        }

        return self::$twig;
    }
}
