<?php

namespace Springbot\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Magento\Framework\App\State;
use Springbot\Queue\Model\Queue;
use Symfony\Component\Console\Input\InputArgument;
use Springbot\Queue\Model\ResourceModel\Job\Collection as JobCollection;

/**
 * Class ListCommand
 *
 * @package Springbot\Queue\Console\Command
 */
class ListCommand extends Command
{
    const PAGE_ARGUMENT = '<page>';
    const PER_PAGE_ARGUMENT = '<per_page>';
    const QUEUE_ARGUMENT = '<queue>';

    private $_queue;
    private $_jobCollection;

    /**
     * @param State $state
     * @param Queue $queue
     * @param JobCollection $jobCollection
     */
    public function __construct(State $state, Queue $queue, JobCollection $jobCollection)
    {
        $this->_queue = $queue;
        $this->_jobCollection = $jobCollection;
        parent::__construct();
    }

    /**
     * Sets config for cli command
     */
    protected function configure()
    {
        $this->setName('springbot:queue:list')
            ->setDescription('List jobs currently in the queue')
            ->addArgument(self::PAGE_ARGUMENT, InputArgument::OPTIONAL, 'Page number', 1)
            ->addArgument(self::PER_PAGE_ARGUMENT, InputArgument::OPTIONAL, 'Results per page', 25)
            ->addArgument(self::QUEUE_ARGUMENT, InputArgument::OPTIONAL, 'Specify a specific queue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $headers = ['queue', 'class', 'method', 'args', 'priority', 'attempts', 'error'];
        $this->_jobCollection->setCurPage($input->getArgument(self::PAGE_ARGUMENT));
        $this->_jobCollection->setPageSize($input->getArgument(self::PER_PAGE_ARGUMENT));
        $this->_jobCollection->addOrder('priority', JobCollection::SORT_ORDER_ASC);
        $this->_jobCollection->addOrder('next_run_at', JobCollection::SORT_ORDER_ASC);

        if ($queue = $input->getArgument(self::QUEUE_ARGUMENT)) {
            $this->_jobCollection->addFieldToFilter('queue', $queue);
        }
        $jobs = $this->_jobCollection->toArray();

        $table = new Table($output);
        $table->setHeaders($headers);

        foreach ($jobs['items'] as $job) {
            $row = [];
            foreach ($headers as $header) {
                $row[] = $job[$header] ?? '';
            }
            $table->addRow($row);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
