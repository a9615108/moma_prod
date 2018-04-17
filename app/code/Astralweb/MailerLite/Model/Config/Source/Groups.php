<?php
namespace Astralweb\MailerLite\Model\Config\Source;

class Groups implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Astralweb\MailerLite\Helper\MailerLite
     */
    protected $mailerLiteHelper;

    /**
     * Groups constructor.
     * @param \Astralweb\MailerLite\Helper\MailerLite $mailerLiteHelper
     */
    public function __construct(
        \Astralweb\MailerLite\Helper\MailerLite $mailerLiteHelper
    ) {
        $this->mailerLiteHelper = $mailerLiteHelper;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $groups = $this->mailerLiteHelper->getGroups();
        $options = [];
        if(count($groups)){
            foreach ($groups as $group) {
                $options[$group->id] = $group->name;
            }
        }
        return $options;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $groups = $this->mailerLiteHelper->getGroups();
        $options[] = ['value' => 0, 'label' =>  __('-- Please Select a Group --')];
        if(count($groups) && !isset($groups->error)){
            foreach ($groups as $group) {
                $options[] = ['value' => $group->id, 'label' => $group->name .' (ID: '. $group->id . ')'];
            }
        }
        return $options;
    }
}