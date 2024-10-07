<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Command\Changelog;

use BK2K\ExtensionHelper\Utility\GitUtility;
use BK2K\ExtensionHelper\Utility\ShellUtility;
use BK2K\ExtensionHelper\Utility\VersionUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this->setName('changelog:create');
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if shell exec is available
        if (!function_exists('shell_exec')) {
            $io->error('Please enable shell_exec and rerun this script.');
            return Command::FAILURE;
        }

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper changelog:create 1.0.0');
            return Command::FAILURE;
        }

        try {
            $tags = GitUtility::getTags();
            $revisionRanges = GitUtility::getRevisionRanges($tags);
            $logs = $this->getLogs($tags, $revisionRanges);
            $this->generateMarkdown($logs, $version);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Changelog created/updated');
        return Command::SUCCESS;
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
            $authors = [];
            foreach ($groups as $group => $commits) {
                if (is_array($commits) && count($commits) > 0) {
                    $content .= "\n## " . $group . "\n\n";
                    foreach ($commits as $commit) {
                        $authors[$commit['authorEmail']] = $commit['author'];
                        $content .= '- ' . $commit['hash'] . ' ' . strip_tags($commit['message']) . "\n";
                    }
                }
            }
            if (count($authors) > 0) {
                $content .= "\n## Contributors\n\n";
                asort($authors);
                foreach ($authors as $authorName) {
                    $content .= '- ' . $authorName . "\n";
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
            'SECURITY',
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
                '%h',   // hash
                '%an',  // author
                '%ae',  // authorEmail
                '%aD',  // date
                '%at',  // timestamp
                '%s',   // message
            ];
            $command = 'git log --pretty="' . implode($splitChar, $format) . '" ' . $query;
            $commits = ShellUtility::outputToArray(ShellUtility::exec($command));
            $formattedCommits = [];
            foreach ($commits as $key => $value) {
                $formattedCommit = explode($splitChar, $value);
                $formattedCommits[] = [
                    'hash' => trim($formattedCommit[0]),
                    'author' => trim($formattedCommit[1]),
                    'authorEmail' => trim($formattedCommit[2]),
                    'date' => trim($formattedCommit[3]),
                    'timestamp' => trim($formattedCommit[4]),
                    'message' => $this->cleanMessage($formattedCommit[5]),
                ];
            }
            $logs[$revisionRange['end']] = [
                'RELEASE' => [],
                'SECURITY' => [],
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
