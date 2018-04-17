<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-sphinx
 * @version   1.0.61
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Mirasvit\Search\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;
use Magento\Framework\App\State as AppState;

class ReindexCommand extends Command
{
    /**
     * @var IndexCollectionFactory
     */
    protected $indexCollectionFactory;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @param IndexCollectionFactory $indexCollectionFactory
     * @param AppState $appState
     */
    public function __construct(
        IndexCollectionFactory $indexCollectionFactory,
        AppState $appState
    ) {
        $this->indexCollectionFactory = $indexCollectionFactory;
        $this->appState = $appState;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:search:reindex')
            ->setDescription('Reindex all search indexes')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $collection = $this->indexCollectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        /** @var \Mirasvit\Search\Model\Index $index */
        foreach ($collection as $index) {
            $output->write($index->getTitle() . ' [' . $index->getCode() . ']....');

            try {
                $index->getIndexInstance()->reindexAll();
                $output->writeln("<info>Done</info>");
            } catch (\Exception $e) {
                $output->writeln("Error");
                $output->writeln($e->getMessage());
            }
        }
    }
}
