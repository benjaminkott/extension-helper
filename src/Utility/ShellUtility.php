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
 * ShellUtility
 */
class ShellUtility
{
    /**
     * @param string $command
     * @return string
     */
    public static function exec(string $command): string
    {
        return (string) shell_exec($command);
    }

    /**
     * @param string $output
     * @return array
     */
    public static function outputToArray(string $output): array
    {
        return array_filter(explode(chr(10), $output));
    }
}
