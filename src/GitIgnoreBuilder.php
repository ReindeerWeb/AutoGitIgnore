<?php

/*
 * This file is part of AutoGitIgnore.
 *
 * (c) ReindeerWeb, Marcel Rudolf, Germany <hello@reindeer-web.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReindeerWeb\AutoGitIgnore;

use Composer\Script\Event;
use ReindeerWeb\ClassHelper\ClassHelper;

/**
 * This class implements the runner for the .gitignore builder
 */
class GitIgnoreBuilder extends ClassHelper
{
    /**
     * This runs the builder
     *
     * @param Composer\Script\Event $event The event which is fired by Composer
     *
     * @return bool Returns false, when there was an error
     */
    public static function Go(Event $event)
    {
        $event->getIO()->writeError('<info>Updating .gitignore: </info>', false);

        $composer = $event->getComposer();
        $repositoryManager = $composer->getRepositoryManager();
        $installManager = $composer->getInstallationManager();

        $packages = array();
        foreach ($repositoryManager->getLocalRepository()->getPackages() as $package) {
            $path = $installManager->getInstallPath($package);
            $packages[] = preg_replace('~^' . preg_quote(str_replace('\\', '/', getcwd()) . '/') . '~', '', str_replace('\\', '/', realpath($path)));
        }

        $packages = array_unique($packages);
        sort($packages);

        try {
            $gitIgnoreFile = GitIgnoreFile::create(getcwd() . DIRECTORY_SEPARATOR . '.gitignore');
            $gitIgnoreFile->setLines($packages);
            $gitIgnoreFile->save();
        } catch (Exception $exception) {
            $event->getIO()->writeError('<info>Failed - ' . $exception->getMessage() . '</info>');

            return false;
        }

        $event->getIO()->writeError('<info>Done - ' . count($packages) . ' packages ignored.</info>');

        return true;
    }
}
