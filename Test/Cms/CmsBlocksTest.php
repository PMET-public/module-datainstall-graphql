<?php

namespace MagentoEse\DataInstallGraphQl\Test\Cms;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class CmsBlocksTest extends GraphQlAbstract
{
    /** @var $identifierQuery */
    protected $identifierQuery
    = <<<QUERY
	query{
		cmsBlocks(identifiers: ["sale-block","gear-block"]) {
			items {
				store_view_code
				title
				identifier
				content:block_content
			}
		}
	}
	QUERY;

    /** @var $idQuery */
    protected $idQuery
    = <<<QUERY
	query{
		cmsBlocks(identifiers: ["13","12"]) {
			items {
				store_view_code
				title
				identifier
				content:block_content
			}
		}
	}
	QUERY;

    public function testBlockByIdentifier()
    {
        $response = $this->graphQlQuery(
            $this->identifierQuery,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedBlocks(), $response, 'blocks query is incorrect');
    }

    public function testBlockById()
    {
        $response = $this->graphQlQuery(
            $this->idQuery,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedBlocks(), $response, 'blocks query is incorrect');
    }

    protected function getExpectedBlocks()
    {
        return [
            'cmsBlocks' => [
                'items' =>[
                    [
                        'store_view_code'=>'default',
                        'title'=>'Sale Block',
                        'identifier'=>'sale-block',
                        // phpcs:ignoreFile Generic.Files.LineLength.TooLong
                        'content'=>"<div class=\"blocks-promo\">\n    <a href=\"{{store url=\"\"}}promotions/women-sale.html\" class=\"block-promo sale-main\">\n        <img src=\"{{media url=\"wysiwyg/luma/sale/sale-main.jpg\"}}\" alt=\"\" />\n        <span class=\"content\">\n            <span class=\"info\">Women’s Deals</span>\n            <strong class=\"title\">Pristine prices on pants, tanks and bras.</strong>\n            <span class=\"more button\">Shop Women’s Deals</span>\n        </span>\n    </a>\n    <div class=\"block-promo-wrapper block-promo-2columns\">\n        <a href=\"{{store url=\"\"}}promotions/men-sale.html\" class=\"block-promo sale-mens\">\n            <img src=\"{{media url=\"wysiwyg/luma/sale/sale-mens.jpg\"}}\" alt=\"\" />\n            <span class=\"content\">\n                <strong class=\"title\">Men’s Bargains</strong>\n                <span class=\"info\">Stretch your budget with active attire</span>\n                <span class=\"more icon\">Shop Men’s Deals</span>\n            </span>\n        </a>\n        <a href=\"{{store url=\"\"}}gear.html\" class=\"block-promo sale-women\">\n            <img src=\"{{media url=\"wysiwyg/luma/sale/sale-gear.jpg\"}}\" alt=\"\" />\n            <span class=\"content\">\n                <strong class=\"title\">Luma Gear Steals</strong>\n                <span class=\"info\">Your best efforts deserve a deal</span>\n                <span class=\"more icon\">Shop Luma Gear</span>\n            </span>\n        </a>\n    </div>\n    <div class=\"block-promo-wrapper block-promo-3columns\">\n        <a class=\"block-promo sale-20-off\">\n            <span class=\"content\">\n                <strong class=\"title\">20% OFF</strong>\n                <span class=\"info\">Every $200-plus purchase!</span>\n            </span>\n            <span class=\"image\"><img src=\"{{media url=\"wysiwyg/luma/sale/sale-20-off.png\"}}\" alt=\"\" /></span>\n        </a>\n        <a class=\"block-promo sale-free-shipping\">\n            <span class=\"content\">\n                <strong class=\"title\">Spend $50 or more&nbsp;&mdash; shipping is free!</strong>\n                <img src=\"{{media url=\"wysiwyg/luma/sale/sale-free-shipping.png\"}}\" alt=\"\" />\n                <span class=\"info\">Buy more, save more</span>\n            </span>\n        </a>\n        <a href=\"{{store url=\"\"}}women/tops-women/tees-women.html\" class=\"block-promo sale-womens-t-shirts\">\n            <span class=\"content\">\n                <strong class=\"title\">You can't have too many tees</strong>\n                <span class=\"info\">4 tees for the price of 3. Right now</span>\n                <span class=\"more icon\">Tees on sale</span>\n            </span>\n            <span class=\"image\"><img src=\"{{media url=\"wysiwyg/luma/womens/womens-t-shirts.png\"}}\" alt=\"\" /></span>\n        </a>\n    </div>\n</div>"
                    ],
                    [
                        'store_view_code'=>'default',
                        'title'=>'Gear Block',
                        'identifier'=>'gear-block',
                        // phpcs:ignoreFile Generic.Files.LineLength.TooLong
                        'content'=>"<div class=\"blocks-promo\">\n    <a href=\"{{store url=\"\"}}gear.html\" class=\"block-promo gear-main\">\n        <img src=\"{{media url=\"wysiwyg/luma/gear/gear-main.jpg\"}}\" alt=\"\" />\n        <span class=\"content\">\n            <strong class=\"title\">Sprite Yoga Companion Kit</strong>\n            <span class=\"info\">Save up to 20% on a&nbsp;bundle!</span>\n            <span class=\"more button\">Shop Yoga Kit</span>\n        </span>\n    </a>\n    <div class=\"block-promo-wrapper block-promo-2columns\">\n        <a href=\"{{store url=\"\"}}gear/fitness-equipment.html\" class=\"block-promo gear-fitnes\">\n            <img src=\"{{media url=\"wysiwyg/luma/gear/gear-fitnes.jpg\"}}\" alt=\"\" />\n            <span class=\"content\">\n                <strong class=\"title\">Loosen Up</strong>\n                <span class=\"info\">Extend your training with yoga straps, tone bands,<br />and jump ropes</span>\n                <span class=\"more icon\">Shop Fitness</span>\n            </span>\n        </a>\n        <a href=\"{{store url=\"\"}}gear/fitness-equipment.html\" class=\"block-promo gear-equipment\">\n            <img src=\"{{media url=\"wysiwyg/luma/gear/gear-equipment.jpg\"}}\" alt=\"\" />\n            <span class=\"content\">\n                <strong class=\"title\">Here’s to you!</strong>\n                <span class=\"info\">$4 Luma water bottle<br />(save&nbsp;70%)</span>\n                <span class=\"note\">Enter promo code H2O<br />at check out</span>\n            </span>\n        </a>\n    </div>\n    <div class=\"block-promo-wrapper block-promo-3columns\">\n        <a href=\"{{store url=\"\"}}gear/bags.html\" class=\"block-promo gear-category-bags\">\n            <span class=\"content\">\n                <strong class=\"title\">Tote, cart or carry</strong>\n                <span class=\"info\">Luma bags go the distance</span>\n                <span class=\"more icon\">Shop Bags</span>\n            </span>\n            <span class=\"image\"><img src=\"{{media url=\"wysiwyg/luma/gear/gear-category-bags.jpg\"}}\" alt=\"\" /></span>\n        </a>\n        <a href=\"{{store url=\"\"}}gear/fitness-equipment.html\" class=\"block-promo gear-category-equipment\">\n            <span class=\"content\">\n                <strong class=\"title\">Let’s get after it!</strong>\n                <span class=\"info\">Luma gym equipment fits your goals and&nbsp;style</span>\n                <span class=\"more icon\">Shop Equipment</span>\n            </span>\n            <span class=\"image\"><img src=\"{{media url=\"wysiwyg/luma/gear/gear-category-equipment.jpg\"}}\" alt=\"\" /></span>\n        </a>\n        <a href=\"{{store url=\"\"}}gear/watches.html\" class=\"block-promo gear-category-watches\">\n            <span class=\"content\">\n                <strong class=\"title\">Luma watches</strong>\n                <span class=\"info\">Keeping pace has never been more stylish</span>\n                <span class=\"more icon\">Shop Watches</span>\n            </span>\n            <span class=\"image\"><img src=\"{{media url=\"wysiwyg/luma/gear/gear-category-watches.jpg\"}}\" alt=\"\" /></span>\n        </a>\n    </div>\n</div>\n<div class=\"content-heading\">\n    <h2 class=\"title\">Hot Sellers</h2>\n    <p class=\"info\">Favorites from Luma shoppers</p>\n</div>\n{{widget type=\"Magento\\CatalogWidget\\Block\\Product\\ProductsList\" products_per_page=\"4\" products_count=\"4\" template=\"product/widget/content/grid.phtml\" conditions_encoded=\"^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`()`,`value`:`24-MB02, 24-WB04, 24-UG06, 24-WG080`^]^]\"}}"
                    ],
                ]
            ]
        ];
    }
}
