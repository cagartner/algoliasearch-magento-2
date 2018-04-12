<?php

namespace Algolia\AlgoliaSearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ConversionAnalyticsMode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'disabled',     'label' => __('[Disabled]')],
            ['value' => 'add_to_cart',  'label' => __('Tract "Add to cart" action as conversion')],
            ['value' => 'place_order',  'label' => __('Tract "Place Order" action as conversion')],
        ];
    }
}
