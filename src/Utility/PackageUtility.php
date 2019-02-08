<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Utility;

/**
 * PackageUtility
 */
class PackageUtility
{
    public static function resolveName(): string
    {
        $name = basename(getcwd());
        if (file_exists('composer.json')) {
            $content = file_get_contents('composer.json');
            $composer = json_decode($content, true);
            if (is_array($composer) && $composer['name']) {
                list(, $name) = explode('/', $composer['name'], 2);
            }
        }
        return $name;
    }
}
