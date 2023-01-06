<?php

namespace MagentoEse\DataInstallGraphQl\Tests\DataInstall;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class ScheduleJobTest extends GraphQlAbstract
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
    
    public function testCreateWithLocalModule()
    {
        // global $expectedStoreConfig;
        // $response = $this->graphQlQuery($this->query,[],'',['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']);
        // $this->assertEquals($expectedStoreConfig, $response['storeConfig'],'storeConfig is incorrect');
        $this->fail('Test Not Written');
    }

    public function testCreateWithLocalFilePath()
    {
        // global $expectedStoreConfig;
        // $response = $this->graphQlQuery($this->query,[],'',['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']);
        // $this->assertEquals($expectedStoreConfig, $response['storeConfig'],'storeConfig is incorrect');
        $this->fail('Test Not Written');
    }

    public function testCreateWithRemoteDataPack()
    {
        // global $expectedStoreConfig;
        // $response = $this->graphQlQuery($this->query,[],'',['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']);
        // $this->assertEquals($expectedStoreConfig, $response['storeConfig'],'storeConfig is incorrect');
        $this->fail('Test Not Written');
    }

    public function testAuthRestriction()
    {
        //Test auth
        //If request doesnt have correct auth, query should throw an error
        self::expectExceptionMessage("GraphQL response contains errors: Authorization header missing or empty\n");
        $response = $this->graphQlQuery($this->query, [], '', ['store'=>'default']);
    }
}
