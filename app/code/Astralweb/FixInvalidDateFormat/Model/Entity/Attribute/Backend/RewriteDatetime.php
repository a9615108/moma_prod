<?php


namespace Astralweb\FixInvalidDateFormat\Model\Entity\Attribute\Backend;


class RewriteDatetime
{
    public function beforeFormatDate(
        \Magento\Eav\Model\Entity\Attribute\Backend\Datetime $datetimeObject,
        $date
    ){
        if (empty($date)) {
            return [$date];
        }
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTime)) {
            //remove ',' to temporarily fix format date;
            $date = str_replace(',','',$date);

            // normalized format expecting Y-m-d[ H:i:s]  - time is optional
            $date = new \DateTime($date);
        }
        return [$date];
    }
}