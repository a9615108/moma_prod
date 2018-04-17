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


namespace Mirasvit\Search\Helper\Stemming;

class Pool
{
    /**
     * @var StemmerInterface[]
     */
    private $pool;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $pool = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->pool = $pool;
    }

    /**
     * @param string $str
     * @return string
     */
    public function singularize($str)
    {
        $locale = $this->localeResolver->getLocale();
        if (isset($this->pool[$locale])) {
            return $this->pool[$locale]->singularize($str);
        }

        return $str;
    }
}