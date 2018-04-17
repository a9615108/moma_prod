<?php
namespace Astralweb\MailerLite\Model\Config\Source;

class Types implements \Magento\Framework\Option\ArrayInterface
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
        $types = $this->mailerLiteHelper->getTypes();
        $options = [];
        if(count($types)){
            foreach ($types as $key=>$type) {
                $options[$key] = $type;
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
        $types = $this->mailerLiteHelper->getTypes();
        $options[] = ['value' => 0, 'label' =>  __('-- Please Select a type --')];
        if(count($types)){
            foreach ($types as $key=>$type) {
                $options[] = ['value' => $key, 'label' => $type];
            }
        }
        return $options;
    }
}