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
 * VersionUtility
 */
class VersionUtility
{
    public static function isValid(?string $version): bool
    {
        if ($version === null) {
            return false;
        }

        if (!preg_match('/\A\d+\.\d+\.\d+\z/', $version)) {
            return false;
        }
        return true;
    }
}
