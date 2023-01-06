<?php

namespace MagentoEse\DataInstallGraphQl\Test\Cms;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

class CmsPagesTest extends GraphQlAbstract
{
    /** @var $identifierQuery */
    protected $identifierQuery
    = <<<QUERY
    query{
        cmsPages(identifiers: ["no-route","private-sales"]){
            items{
                store_view_code
                identifier
                title
                page_layout
                content_heading
                content:page_content
                meta_description
                meta_keywords
                meta_title
            }
        }
    }
    QUERY;

    /** @var $identifierQuery */
    protected $idQuery
    = <<<QUERY
    query{
        cmsPages(identifiers: ["1","6"]){
            items {
                store_view_code
                identifier
                title
                page_layout
                content_heading
                content:page_content
                meta_description
                meta_keywords
                meta_title
            }
        }
    }
    QUERY;

    public function testPageByIdentifier()
    {
        $response = $this->graphQlQuery(
            $this->identifierQuery,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedBlocks(), $response, 'blocks query is incorrect');
    }

    public function testPageById()
    {
        $response = $this->graphQlQuery(
            $this->idQuery,
            [],
            '',
            ['Authorization'=>TESTS_WEBSERVICE_USER.'|'.TESTS_WEBSERVICE_APIKEY,'store'=>'default']
        );
        $this->assertEquals($this->getExpectedBlocks(), $response, 'blocks query is incorrect');
    }

    public function testAuthRestriction()
    {
        //Test auth
        //If request doesnt have correct auth, query should throw an error
        self::expectExceptionMessage("GraphQL response contains errors: Authorization header missing or empty\n");
        $response = $this->graphQlQuery($this->idQuery, [], '', ['store'=>'default']);
    }

    protected function getExpectedBlocks()
    {
        return [
            'cmsPages' => [
                'items' =>[
                    [
                        'store_view_code'=>'default',
                        'identifier'=>'no-route',
                        'title'=>'404 Not Found',
                        'page_layout'=>'2columns-right',
                        'content_heading'=>'Whoops, our bad...',
                        // phpcs:ignoreFile Generic.Files.LineLength.TooLong
                        'content'=>"<dl>\r\n<dt>The page you requested was not found, and we have a fine guess why.</dt>\r\n<dd>\r\n<ul class=\"disc\">\r\n<li>If you typed the URL directly, please make sure the spelling is correct.</li>\r\n<li>If you clicked on a link to get here, the link is outdated.</li>\r\n</ul></dd>\r\n</dl>\r\n<dl>\r\n<dt>What can you do?</dt>\r\n<dd>Have no fear, help is near! There are many ways you can get back on track with Magento Store.</dd>\r\n<dd>\r\n<ul class=\"disc\">\r\n<li><a href=\"#\" onclick=\"history.go(-1); return false;\">Go back</a> to the previous page.</li>\r\n<li>Use the search bar at the top of the page to search for your products.</li>\r\n<li>Follow these links to get you back on track!<br /><a href=\"{{store url=\"\"}}\">Store Home</a> <span class=\"separator\">|</span> <a href=\"{{store url=\"customer/account\"}}\">My Account</a></li></ul></dd></dl>\r\n",
                        'meta_description'=>'Page description',
                        'meta_keywords'=>'Page keywords',
                        'meta_title'=> null
                    ],
                    [
                        'store_view_code'=>'default',
                        'identifier'=>'private-sales',
                        'title'=>'Welcome to our Exclusive Online Store',
                        'page_layout'=>'1column',
                        'content_heading'=>null,
                        // phpcs:ignoreFile Generic.Files.LineLength.TooLong
                        'content'=>"<div class=\"private-sales-index\">\n        <div class=\"box\">\n        <div class=\"content\">\n        <h1>Welcome to our Exclusive Online Store</h1>\n        <p>If you are a registered member, please <a href=\"{{store url=\"customer/account/login\"}}\">sign in here</a>.</p>\n        </div>\n        </div>\n        </div>",
                        'meta_description'=> null,
                        'meta_keywords'=> null,
                        'meta_title'=> null
                    ],
                ]
            ]
        ];
    }
}
