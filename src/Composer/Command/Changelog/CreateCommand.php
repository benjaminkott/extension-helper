<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Composer\Command\Changelog;

use BK2K\ExtensionHelper\Utility\GitUtility;
use BK2K\ExtensionHelper\Utility\ShellUtility;
use BK2K\ExtensionHelper\Utility\VersionUtility;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends BaseCommand
{
    protected static $defaultName = 'changelog:create';

    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Generate Changelog');
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('version', InputArgument::REQUIRED)
            ])
        );
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Check if shell exec is available
        if (!function_exists('shell_exec')) {
            $io->error('Please enable shell_exec and rerun this script.');
            exit(1);
        }

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper changelog:create 1.0.0');
            exit(1);
        }

        try {
            $tags = GitUtility::getTags();
            $revisionRanges = GitUtility::getRevisionRanges($tags);
            $logs = $this->getLogs($tags, $revisionRanges);
            $this->generateMarkdown($logs, $version);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            exit(1);
        }

        $io->success('Changelog created/updated');
    }

    /**
     * @param array $logs
     * @param string $nextVersion
     * @throws \RuntimeException
     */
    public function generateMarkdown($logs, $nextVersion)
    {
        // Prepare content
        $content = '';
        foreach ($logs as $version => $groups) {
            if ($version === 'HEAD') {
                $version = $nextVersion;
            }
            $content .= '# ' . $version . "\n";
            foreach ($groups as $group => $commits) {
                if (is_array($commits) && count($commits) > 0) {
                    $content .= "\n## " . $group . "\n\n";
                    foreach ($commits as $commit) {
                        $content .= '- ' . strip_tags($commit['message']) . ' ' . $commit['hash'] . "\n";
                    }
                }
            }
            $content .= "\n";
        }
        // Write file
        $file = fopen('CHANGELOG.md', 'w+');
        if (!$file) {
            throw new \RuntimeException('Unable to create CHANGELOG.md', 1496156839);
        }
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * @param string $character
     * @param int $count
     * @param bool $fill
     * @return string
     */
    public function generateLine(string $character = ' ', int $count = 0, bool $fill = false): string
    {
        $output = '';
        if ($fill) {
            $count += 2;
        }
        while ($count > 0) {
            $output .= $character;
            $count--;
        }
        return $output;
    }

    /**
     * @param array $logs
     * @return array
     */
    public function filterLogs(array $logs): array
    {
        $blacklist = [
            'Set version to',
            'Merge pull request',
            'Merge branch',
            'Scrutinizer Auto-Fixer',
            '[FOLLOWUP]',
            '[RELEASE]'
        ];
        $categories = [
            'BUGFIX',
            'TASK',
            'FEATURE'
        ];
        foreach ($logs as $version => $entries) {
            foreach ($entries['MISC'] as $logKey => $log) {
                foreach ($blacklist as $blacklistedValue) {
                    if (strpos($log['message'], $blacklistedValue) !== false) {
                        unset($logs[$version]['MISC'][$logKey]);
                        continue 2; // process next entry, jump out of both foreach
                    }
                }
                if (strpos($log['message'], '!!!') !== false) {
                    $logs[$version]['BREAKING'][] = $log;
                    unset($logs[$version]['MISC'][$logKey]);
                }
                foreach ($categories as $key) {
                    if (strpos($log['message'], '[' . $key . ']') !== false) {
                        $logs[$version][$key][] = $log;
                        unset($logs[$version]['MISC'][$logKey]);
                    }
                }
            }
        }
        return $logs;
    }

    /**
     * @param array $tags
     * @param array $revisionRanges
     * @throws \RuntimeException
     * @return array
     */
    public function getLogs(array $tags, array $revisionRanges): array
    {
        if (count($tags) === 0) {
            throw new \RuntimeException('Does not have any tags', 1496158152);
        }
        $splitChar = '###SPLIT###';
        $logs = [];
        foreach ($revisionRanges as $revisionRange) {
            $query = $revisionRange['end'] . (isset($revisionRange['start']) ? '...' . $revisionRange['start'] : '');
            $format = [
                '%h',
                '%an',
                '%s',
                '%aD',
                '%at'
            ];
            $command = 'git log --pretty="' . implode($splitChar, $format) . '" ' . $query;
            $commits = ShellUtility::outputToArray(ShellUtility::exec($command));
            $formattedCommits = [];
            foreach ($commits as $key => $value) {
                $formattedCommit = explode($splitChar, $value);
                $formattedCommits[] = [
                    'hash' => $formattedCommit[0],
                    'date' => $formattedCommit[3],
                    'timestamp' => $formattedCommit[4],
                    'author' => $formattedCommit[1],
                    'message' => $this->cleanMessage($formattedCommit[2])
                ];
            }
            $logs[$revisionRange['end']] = [
                'RELEASE' => [],
                'BREAKING' => [],
                'FEATURE' => [],
                'TASK' => [],
                'BUGFIX' => [],
                'MISC' => $formattedCommits
            ];
        }
        return $this->filterLogs($logs);
    }

    /**
     * @param string $message
     * @return string
     */
    public function cleanMessage(string $message): string
    {
        return trim(str_replace('…', '...', $message));
    }
}
