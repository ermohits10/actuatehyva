/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    config: {
        mixins: {
            "Magento_Catalog/js/product/addtocart-button" : {
                "Bss_HidePrice/js/product/addtocart-button": true
            },
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_HidePrice/js/swatch-renderer": true
            },
            "Magento_ConfigurableProduct/js/configurable" : {
                "Bss_HidePrice/js/configurable": true
            },
            "Magento_Bundle/js/price-bundle" : {
                "Bss_HidePrice/js/price-bundle" : true
            }
        }
    },
    "map": {
        "*": {
            "Magento_Catalog/template/product/addtocart-button.html":
                "Bss_HidePrice/template/product/addtocart-button.html"
        }
    }
};