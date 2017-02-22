<?php
/**
 * Tweakwise & Emico (https://www.tweakwise.com/ & https://www.emico.nl/) - All Rights Reserved
 *
 * @copyright Copyright (c) 2017-2017 Tweakwise.com B.V. (https://www.tweakwise.com)
 * @license   Proprietary and confidential, Unauthorized copying of this file, via any medium is strictly prohibited
 */

namespace Emico\TweakwiseExport\Console\Command;

use Emico\TweakwiseExport\Model\Config;
use Emico\TweakwiseExport\Model\Export;
use Emico\TweakwiseExport\Model\Validate\Validator;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Export
     */
    protected $export;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var State
     */
    protected $state;

    /**
     * ExportCommand constructor.
     *
     * @param Config $config
     * @param Export $export
     * @param Validator $validator
     * @param State $state
     */
    public function __construct(Config $config, Export $export, Validator $validator, State $state)
    {
        $this->config = $config;
        $this->export = $export;
        $this->validator = $validator;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tweakwise:export')
            ->addArgument('file', InputArgument::OPTIONAL, 'Export to specific file', $this->config->getDefaultFeedPath())
            ->addOption('validate', 'c', InputOption::VALUE_NONE, 'Validate feed and rollback if fails.')
            ->setDescription('Export tweakwise feed');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $feedFile = (string) $input->getArgument('file');
        $validate = (bool) $input->getOption('validate');

        $startTime = microtime(true);
        $this->export->generateToFile($feedFile, $validate);
        $generateTime = round(microtime(true) - $startTime, 2);
        $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $output->writeln(sprintf('Feed written to %s in %ss using %sMb memory', $feedFile, $generateTime, $memoryUsage));
    }
}