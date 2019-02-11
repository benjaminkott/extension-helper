<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Command\Version;

use BK2K\ExtensionHelper\Utility\VersionUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetCommand extends Command
{
    protected static $defaultName = 'version:set';

    protected function configure()
    {
        $this->setDescription('Set Version');
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('version', InputArgument::REQUIRED),
                new InputOption('dev', InputOption::VALUE_NONE)
            ])
        );
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper version:set 1.0.0');
            exit(1);
        }

        // Add -dev affix to version number
        if ($input->getOption('dev')) {
            $version .= '-dev';
        }

        $files = [
            'Documentation/Settings.cfg' => [
                'pattern' => '((version|release)[^\S\n]*=[^\S\n]*)'
            ],
            'Documentation/Settings.yml' => [
                'pattern' => '((version|release)[^\S\n]*:[^\S\n]*)'
            ],
            'ext_emconf.php' => [
                'pattern' => '((\'|")version(\'|")([^\S\n]*=>[^\S\n]*)(\'|"))'
            ]
        ];

        $counter = 0;
        foreach ($files as $file => $config) {
            if (file_exists($file) && isset($config['pattern'])) {
                $content = file_get_contents($file);
                $content = preg_replace(
                    '/' . $config['pattern'] . '\d+\.\d+\.\d+(-dev)?/',
                    '${1}' . $version,
                    $content,
                    -1,
                    $count
                );
                if ($count) {
                    $counter++;
                    file_put_contents($file, $content, LOCK_EX);
                    $io->writeln('- ' . $file . ' was set to version ' . $version);
                }
            }
        }

        if ($counter > 0) {
            $io->success('Version set to ' . $version);
        }
    }
}
