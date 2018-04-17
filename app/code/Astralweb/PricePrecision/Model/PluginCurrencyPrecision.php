<?php


namespace Astralweb\PricePrecision\Model;


class PluginCurrencyPrecision
{
    public function beforeFormatPrecision(
        \Magento\Directory\Model\Currency $currency,
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    )
    {
        if ($price != 0) {
            if (intval($price) / $price == 1) {
                $price = intval($price);
                $precision = 0;
            }
        }
        return [
            $price,
            $precision,
            $options,
            $includeContainer,
            $addBrackets
        ];
    }
}