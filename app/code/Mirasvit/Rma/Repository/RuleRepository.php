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
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Repository;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rma\Model\Rule;

class RuleRepository implements \Mirasvit\Rma\Api\Repository\RuleRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Rule[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Mirasvit\Rma\Model\RuleFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Rule $ruleResource,
        \Mirasvit\Rma\Api\Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->objectFactory         = $objectFactory;
        $this->ruleResource          = $ruleResource;
        $this->searchResultsFactory  = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\RuleInterface $rule)
    {
        $this->ruleResource->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->instances[$ruleId])) {
            /** @var Rule $rule */
            $rule = $this->objectFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                throw NoSuchEntityException::singleRule('id', $ruleId);
            }
            $this->instances[$ruleId] = $rule;
        }

        return $this->instances[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\RuleInterface $rule)
    {
        try {
            $ruleId = $rule->getId();
            $this->ruleResource->delete($rule);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete rule with id %1',
                    $rule->getId()
                ),
                $e
            );
        }
        unset($this->instances[$ruleId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $rule = $this->get($ruleId);

        return  $this->delete($rule);
    }

    /**
     * Validate rule process
     *
     * @param  Rule $rule
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateRule(Rule $rule)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var \Mirasvit\Rma\Model\ResourceModel\Rule\Collection $collection */
        return $this->ruleCollectionFactory->create();
    }
}
