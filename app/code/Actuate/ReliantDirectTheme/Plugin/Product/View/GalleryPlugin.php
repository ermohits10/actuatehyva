<?php

namespace Actuate\ReliantDirectTheme\Plugin\Product\View;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Framework\Serialize\Serializer\Json;

class GalleryPlugin
{
    private Json $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param Gallery $subject
     * @param $result
     * @return bool|string
     */
    public function afterGetGalleryImagesJson(Gallery $subject, $result): bool|string
    {
        $imagesItems = $this->serializer->unserialize($result);
        $imagesItems = array_reverse($imagesItems, true);
        return $this->serializer->serialize($imagesItems);
    }
}
