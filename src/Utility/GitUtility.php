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
 * GitUtility
 */
class GitUtility
{
    /**
     * @return array
     */
    public static function getTags(): array
    {
        $tags = ShellUtility::outputToArray(ShellUtility::exec('git tag -l --sort=-v:refname --merged'));
        array_unshift($tags, 'HEAD');
        return $tags;
    }

    /**
     * @param array $tags
     * @return array
     */
    public static function getRevisionRanges(array $tags): array
    {
        $previous = null;
        $revisionRanges = [];
        foreach ($tags as $key => $value) {
            if (strpos($value, 'v') !== 0) {
                if ($previous !== null) {
                    $revisionRanges[$previous]['start'] = $value;
                }
                $revisionRanges[$key]['end'] = $value;
                $previous = $key;
            }
        }
        return $revisionRanges;
    }
}
