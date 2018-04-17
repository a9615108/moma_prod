<?php


namespace Astralweb\RestrictedTimeProduct\Block\Adminhtml\Product\DateTime;


class DateTimePicker extends \Magento\Framework\Data\Form\Element\Date
{
    public function getElementHtml(){
        //die('22');
        $this->addClass('admin__control-text  input-text both-date-time');
        $dateFormat = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
        if (empty($dateFormat)) {
            throw new \Exception(
                'Output format is not specified. ' .
                'Please specify "format" key in constructor, or set it using setFormat().'
            );
        }

        $dataInit = 'data-mage-init="' . $this->_escape(
                json_encode(
                    [
                        'calendar' => [
                            'dateFormat' => $dateFormat,
                            'showsTime' =>true,
                            'timeFormat' => $timeFormat,
                            'buttonImage' => $this->getImage(),
                            'buttonText' => 'Select Date',
                            'disabled' => $this->getDisabled(),
                        ],
                    ]
                )
            ) . '"';

        $html = sprintf(
            '<input onfocus="console.log(\'focused\')" name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}