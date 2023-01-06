<?php

namespace MagentoEse\DataInstallGraphQl\Test\Cms;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class ContentConversionTest extends GraphQlAbstract
{
    /** @var $query */
    protected $query
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

    /**
     * @magentoDataFixture MagentoEse_DataInstallGraphQl::Test/_files/categories.php
     */
    public function testContentConversion()
    {
        // $response = $this->graphQlQuery(
        //     $this->query,
        //     [],
        //     '',
        //     ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        // );
        // $this->assertEquals($this->getExpectedCategories(), $response, 'categories query is incorrect');
        $this->fail('Test Not Written');
    }

    protected function getExpected()
    {
        return [];
    }
}
