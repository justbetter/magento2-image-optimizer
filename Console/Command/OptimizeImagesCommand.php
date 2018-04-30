<?php
namespace JustBetter\ImageOptimizer\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JustBetter\ImageOptimizer\Helper\Data;
use Psr\Log\LoggerInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Class OptimizeImagesCommand
 *
 * @package JustBetter\ImageOptimizer\Console\Command
 */
class OptimizeImagesCommand extends Command
{
    protected $_includeFiles = [
        'jpeg',
        'jpg',
        'png',
        'gif'
    ];

    protected $_excludeDirs = [
        '(cache)',
        '(tmp)',
        '(.thumbs)'
    ];

    /**
     * @var Filesystem
     */
    protected $_filesystem;

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
     * @param Filesystem      $_filesystem
     * @param LoggerInterface $logger
     * @param Data            $imageOptimizerHelper
     */
    public function __construct(
        Filesystem $_filesystem,
        LoggerInterface $logger,
        Data $imageOptimizerHelper
    )
    {
        $this->_filesystem = $_filesystem;
        $this->imageOptimizerHelper = $imageOptimizerHelper;
        $this->config = $imageOptimizerHelper->collectModuleConfig();
        $this->logger= $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('justbetter:imageoptimizer:optimizeall')
                ->setDescription('Re-save all images with spatie package');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start Optimize all images</info>');

        $dir = new \RecursiveDirectoryIterator(
            $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        $this->loopThrougDir($dir, $input, $output);
    }

    protected function loopThrougDir($dir, InputInterface $input, OutputInterface $output)
    {
        foreach ($dir as $fullPath => $fileinfo) {
            if ($fileinfo->isDir()){
                $this->loopThrougDir(
                    new \RecursiveDirectoryIterator(
                        $fileinfo->getPathName(),
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    $input,
                    $output
                );
            }

            if (preg_match('~\.('.implode('|',$this->_includeFiles).')$~', $fullPath) &&
                ! preg_match('/'.implode('|', $this->_excludeDirs).'/', $fullPath)) {

                $this->optimizeImage($fullPath, $input, $output);
            }
        }
    }

    protected function optimizeImage($image, InputInterface $input, OutputInterface $output)
    {
        $optimizerChain = OptimizerChainFactory::create();

        if (array_key_exists('log', $this->config) && $this->config['log']) {
            $optimizerChain = $optimizerChain->useLogger($this->logger);
        }

        try {
            $optimizerChain->optimize($image);
            $output->writeln('<info>Image: '.$image.' is optimized</info>');
        } catch(\Exception $e) {
            $output->writeln('<error>Image not optimized, error: '.$e->getMessage());
        }
    }
}
