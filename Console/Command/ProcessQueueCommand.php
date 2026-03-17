<?php

namespace Springbot\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Springbot\Queue\Model\Queue;

/**
 * Class ProcessQueueCommand
 *
 * @package Springbot\Queue\Console\Command
 */
class ProcessQueueCommand extends Command
{

    private $_state;
    private $_queue;

    /**
     * @param State $state
     * @param Queue $queue
     */
    public function __construct(State $state, Queue $queue)
    {
        $this->_state = $state;
        $this->_queue = $queue;

        parent::__construct();
    }

    /**
     * Sets config for cli command
     */
    protected function configure()
    {
        $this->setName('springbot:queue:process')
            ->setDescription('Process Queue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->_state->getAreaCode();
        } catch (\Throwable $e) {
            $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }

        $success = $this->_queue->runNextJob();
        
        if ($success === true) {
            $output->writeln("Queue Processed.");
        } elseif ($success === false) {
            $output->writeln("Job failed.");
        } else {
            $output->writeln("No jobs in queue.");
        }
    }
}
