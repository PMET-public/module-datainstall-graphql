# Functional testing

##### GraphQL testing documentation
<https://devdocs.magento.com/guides/v2.3/graphql/functional-testing.html>

####Configuration
Copy `dev/tests/api-functional/phpunit_graphql.xml.dist` to `dev/tests/api-functional/phpunit_graphql.xml`

Make the following edits. In our current deployment `TESTS_WEBSERVICE_USER` is the username of a valid magento admin user (likely **admin**). `TESTS_WEBSERVICE_APIKEY` is the password for that admin user

    <!-- Webserver URL -->
    <const name="TESTS_BASE_URL" value="http://magento.url"/>
    <!-- Webserver API user -->
    <const name="TESTS_WEBSERVICE_USER" value="admin"/>
    <!-- Webserver API key -->
    <const name="TESTS_WEBSERVICE_APIKEY" value="123123q"/>

####Running tests
#####Run all tests
`vendor/bin/phpunit -c dev/tests/api-functional/phpunit_graphql.xml vendor/magentoese/module-data-install-graph-ql`

#####Run tests for a data type
example:`vendor/bin/phpunit -c dev/tests/api-functional/phpunit_graphql.xml vendor/magentoese/module-data-install-graph-ql/Test/Cms`

#####Run single test
`vendor/bin/phpunit  -c dev/tests/api-functional/phpunit_graphql.xml vendor/magentoese/module-data-install-graph-ql/Test/Cms/CmsBlocksTest.php`

add the ` --testdox ` option for more verbose output