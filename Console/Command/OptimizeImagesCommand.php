<?php

namespace JustBetter\ImageOptimizer\Console\Command;

use Exception;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use Magento\Framework\Filesystem;
use Spatie\ImageOptimizer\OptimizerChain;
use JustBetter\ImageOptimizer\Helper\Data;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OptimizeImagesCommand
 *
 * @package JustBetter\ImageOptimizer\Console\Command
 */
class OptimizeImagesCommand extends Command
{
    protected $includeFiles = [
        'jpeg',
        'jpg',
        'png',
        'gif'
    ];

    protected $excludeDirs = [
        '(cache)',
        '(tmp)',
        '(.thumbs)'
    ];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $imageOptimizerHelper;

    /**
     * @var array
     */
    protected $config;

    /**
     * OptimizeImagesCommand constructor.
     *
     * @param Filesystem      $filesystem
     * @param LoggerInterface $logger
     * @param Data            $imageOptimizerHelper
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        Data $imageOptimizerHelper
    ) {
        $this->filesystem = $filesystem;
        $this->imageOptimizerHelper = $imageOptimizerHelper;
        $this->config = $imageOptimizerHelper->collectModuleConfig();
        $this->logger= $logger;
        parent::__construct();
    }

    protected function configure()
    {
        /**
         *   Possible parameters to overwrite magento 2 options
         *  'compression' => 'Jpeg Compression (number 85)',
         *  'strip_all' => 'Jpeg Strip all (true or false)',
         *  'all_progressive' => 'Jpeg All progressive (true or false)',
         *  'quality_min_max' => 'PNG min and max quality (e.g. 65-80)',
         *  'interlace' => 'PNG interlace (true or false)',
         *  'optimization_level' => 'PNG optimization level (0 to 7)',
         */
        foreach ($this->imageOptimizerHelper->cliConfigKeys as $name => $value) {
            $options[] =  new InputOption(
                $name,
                null,
                InputOption::VALUE_OPTIONAL,
                $value
            );
        }

        $this->setName('justbetter:imageoptimizer:optimizeall')
            ->setDescription('Re-save all images with spatie package')
            ->setDefinition($options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start Optimize all images</info>');

        $dir = new RecursiveDirectoryIterator(
            $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        $this->loopThrougDir($dir, $input, $output);
    }

    protected function loopThrougDir($dir, InputInterface $input, OutputInterface $output)
    {
        foreach ($dir as $fullPath => $fileinfo) {
            if ($fileinfo->isDir()) {
                $this->loopThrougDir(
                    new RecursiveDirectoryIterator(
                        $fileinfo->getPathName(),
                        RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    $input,
                    $output
                );
            }

            if (preg_match('~\.('.implode('|', $this->includeFiles).')$~', $fullPath) &&
                ! preg_match('/'.implode('|', $this->excludeDirs).'/', $fullPath)) {
                $this->optimizeImage($fullPath, $input, $output);
            }
        }
    }

    protected function optimizeImage($image, InputInterface $input, OutputInterface $output)
    {
        $overWriteOptions = [];

        foreach ($this->imageOptimizerHelper->cliConfigKeys as $name => $notused) {
            $overWriteOptions[$name] = $input->getOption($name);
        }

        $optimizerChain = $this->imageOptimizerHelper->customOptimizerChain($overWriteOptions);

        if (array_key_exists('log', $this->config) && $this->config['log']) {
            $optimizerChain = $optimizerChain->useLogger($this->logger);
        }

        try {
            $optimizerChain->optimize($image);
            $output->writeln('<info>Image: '.$image.' is optimized</info>');
        } catch (Exception $e) {
            $output->writeln('<error>Image not optimized, error: '.$e->getMessage());
        }
    }
}
