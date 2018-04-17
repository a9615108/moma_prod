<?php
namespace Astralweb\Contactus\Model\Contact\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    const ENABLED  = 1;
    const DISABLED = 0;

    public function toOptionArray()
    {
        $arrs = array
        (
            array('value'=>1,'label'=>'Enabled'),
            array('value'=>0,'label'=>'Disabled')
        );

        foreach($arrs as $arr){
            $options[] =
                array(
                    'label' => $arr['label'],
                    'value' => $arr['value']
                );
        }
        return $options;
    }
}