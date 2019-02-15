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
     * @throws \InvalidArgumentException
     * @return string $filename;
     */
    public static function getArchive($version = null): string
    {
        $packageName = PackageUtility::resolveName();
        $name = str_replace('-', '_', $packageName);
        $name = str_replace(' ', '_', $name);

        if ($version && !self::isTag($version)) {
            throw new \InvalidArgumentException('Version ' . $version . ' does not exist');
        }
        if (!$version) {
            $tag = trim(ShellUtility::exec('git tag -l --points-at HEAD'));
            if ($tag) {
                $version = $tag;
            } else {
                $version = ShellUtility::exec('git rev-parse --abbrev-ref HEAD');
                $revision = ShellUtility::exec('git rev-parse --short HEAD');
            }
        }

        $filename = $name . '_' . $version . (!empty($revision) ? '-' . $revision : '') . '.zip';
        ShellUtility::exec('git archive ' . $version . ' --format zip --output ' . $filename);

        return $filename;
    }

    /**
     * @param string $files
     */
    public static function stage(array $files = ['.'])
    {
        foreach ($files as $filename) {
            ShellUtility::exec('git add ' . $filename);
        }
    }

    /**
     * @param string $message
     */
    public static function commit(string $message)
    {
        ShellUtility::exec('git commit -m "' . $message . '"');
    }

    /**
     * @param string $tag
     * @throws \InvalidArgumentException
     */
    public static function addTag(string $tag)
    {
        if (self::isTag($tag)) {
            throw new \InvalidArgumentException('The tag "' . $tag . '" already exists');
        }
        ShellUtility::exec('git tag ' . $tag);
    }

    /**
     * @param string $version
     * @return bool
     */
    public static function isTag(string $version): bool
    {
        return in_array($version, self::getTags());
    }

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
