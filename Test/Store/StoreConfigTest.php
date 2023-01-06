<?php

namespace MagentoEse\DataInstallGraphQl\Test\Store;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class StoreConfigTest extends GraphQlAbstract
{
    /** @var $query */
    protected $query
    = <<<QUERY
	{
		storeConfig {
			is_default_view:is_default_store
			is_default_store:is_default_store_group
			root_category_id
			root_category_uid
			store_view_code:store_code
			store_code:store_group_code
			store_name:store_group_name
			view_name:store_name
			view_order:store_sort_order
			site_code:website_code
			site_name:website_name
			store_root_category:root_cateogry_name
			theme
			theme_fallback
		}
	}
	QUERY;
    
    public function testStoreConfig()
    {
        $response = $this->graphQlQuery(
            $this->query,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedStoreConfig(), $response['storeConfig'], 'storeConfig is incorrect');
    }

    public function testAuthRestriction()
    {
        //Test auth
        //If request doesnt have correct auth, query should throw an error
        self::expectExceptionMessage("GraphQL response contains errors: Authorization header missing or empty\n");
        $response = $this->graphQlQuery($this->query, [], '', ['store'=>'default']);
    }

    protected function getExpectedStoreConfig()
    {
        return [
            'is_default_view'=>true,
            'is_default_store'=>true,
            'root_category_id'=>2,
            'root_category_uid'=>'Mg==',
            'store_view_code'=>'default',
            'store_code'=>'main_website_store',
            'store_name'=>'Main Website Store',
            'view_name'=>'Default Store View',
            'view_order'=>0,
            'site_code'=>'base',
            'site_name'=>'Main Website',
            'store_root_category'=>'Default Category',
            'theme'=>'Magento/luma',
            'theme_fallback'=>'Magento/luma'
        ];
    }
}
