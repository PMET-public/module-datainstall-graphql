<?php

namespace MagentoEse\DataInstallGraphQl\Test\Category;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class CategoriesTest extends GraphQlAbstract
{
    /** @var $query */
    protected $query
    = <<<QUERY
	query{
		categories(filters: {  parent_id: {in: ["3","4","8"]}} pageSize:50) {
		items {
			id
			store_view_code
			original_name:name
			name:category_name
			path:name_path
			is_active
			landing_page_id:landing_page
			landing_page:landing_page_identifier
			page_layout
			custom_design_id:custom_design
			custom_design:custom_design_theme
			}
		}
	}
    QUERY;
    /**
     * @magentoDataFixture MagentoEse_DataInstallGraphQl::Test/_files/categories.php
     */
    public function testCategories()
    {
        $response = $this->graphQlQuery(
            $this->query,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedCategories(), $response, 'categories query is incorrect');
    }

    protected function getExpectedCategories()
    {
        return [
            'categories' => [
                'items' =>[
                    [
                        'id'=> 4,
                        'store_view_code'=> 'default',
                        'original_name'=> 'Bags',
                        'name'=> 'Bags',
                        'path'=> 'Gear',
                        'is_active'=> true,
                        'landing_page'=> null,
                        'page_layout'=> null,
                        'custom_design_id'=> null,
                        'custom_design'=> null,
                        'landing_page_id'=>null
                    ],
                    [
                        'id'=> 5,
                        'store_view_code'=> 'default',
                        'original_name'=> 'Fitness Equipment',
                        'name'=> 'Fitness Equipment',
                        'path'=> 'Gear',
                        'is_active'=> true,
                        'landing_page'=> null,
                        'page_layout'=> null,
                        'custom_design_id'=> null,
                        'custom_design'=> null,
                        'landing_page_id'=>null
                    ],
                    [
                        'id'=> 6,
                        'store_view_code'=> 'default',
                        'original_name'=> 'Watches',
                        'name'=> 'Watches',
                        'path'=> 'Gear',
                        'is_active'=> true,
                        'landing_page'=>'gear-block',
                        'page_layout'=>'2columns-left',
                        'custom_design_id'=>'2',
                        'custom_design'=>'Magento/luma',
                        'landing_page_id'=>12
                    ]
                ]
            ]
        ];
    }
}
