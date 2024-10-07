<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Command\Release;

use BK2K\ExtensionHelper\Utility\GitUtility;
use BK2K\ExtensionHelper\Utility\VersionUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PublishCommand extends Command
{
    protected function configure()
    {
        $this->setName('release:publish');
        $this->setDescription('Commit current changes, and tag the commit');
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

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper release:create 1.0.0');
            return Command::FAILURE;
        }

        try {
            GitUtility::stage();
            GitUtility::commit('[RELEASE] v' . $version);
            GitUtility::addTag($version);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Release v' . $version . ' created');
        return Command::SUCCESS;
    }
}
