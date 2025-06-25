<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Console;

use Flarum\Foundation\Console\AssetsPublishCommand;
use Flarum\Foundation\Paths;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CopyAssetsCommand extends Command
{
    protected $signature = 'fof:s3:copy 
        {--move : Delete local files after moving to the S3 disk}';

    protected $description = 'Copy assets to the S3 disk';

    public function handle(Container $container, Factory $factory, Paths $paths, AssetsPublishCommand $publishCommand)
    {
        /** @var Filesystem $localFilesystem */
        $localFilesystem = $container->make('files');

        // Determine if files should be deleted after moving
        $deleteAfterMove = $this->option('move');

        // Move assets
        $this->info($deleteAfterMove ? 'Moving assets...' : 'Copying assets...');
        $this->moveFilesToDisk($localFilesystem, $paths->public.'/assets', $factory->disk('flarum-assets'), $deleteAfterMove);

        $publishCommand->run(
            new ArrayInput([]),
            new ConsoleOutput()
        );
    }

    /**
     * Get the registered disks.
     *
     * @return array
     */
    protected function getFlarumDisks(): array
    {
        return resolve('flarum.filesystem.disks');
    }

    protected function moveFilesToDisk(Filesystem $localFilesystem, string $localPath, Cloud $disk, bool $deleteAfterMove): void
    {
        $count = count($localFilesystem->allFiles($localPath));
        $this->output->progressStart($count);

        foreach ($localFilesystem->allFiles($localPath) as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $written = $disk->put($file->getRelativePathname(), $file->getContents());

            if ($written) {
                if ($deleteAfterMove) {
                    $localFilesystem->delete($file->getPathname());
                }
                $this->output->progressAdvance();
            } else {
                throw new \Exception('File did not copy');
            }
        }

        $this->output->progressFinish();
    }
}
