# Magento 2 Data Install Module GraphQl

This module provides GraphQL support for the Data Install Module. There are two sets of functionality.

1. Data Installer functions like launching a data pack import and retrieving log information about an import
2. Exporting of data via a GraphQl query to be used in a data pack

For most queries and mutations, a valid admin user is required.  In the reqeust headers, add a header of `Authorization` with a value of `username|password`. Or the value can be added in the Commerce UI under Stores->Configuration->Advanced->System->Data Installer Authorization. This will overide the value passed in the header

Details on query arguments and types are available in the online GraphQL docs. 

## Data Installer Support

**createDataInstallerJob**: Similiar to installing a data pack via CLI. This will schedule a data pack import, and return the `job_id` of the process. This also supports the retrieval of remote data packs from GitHub. If you are accessing a private repository, you will need to create a [Personal Access Token](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token). This token can be used as an option in the mutation. Or it can be added to the Commerce instance under *Stores->Configuration->Advanced->System->Data Installer Authorization*. 
  - When setting up the token, create a "classic" token, and check the box to give access to the `repo` scope

*example: data pack on instance*

	mutation {
      createDataInstallerJob(input: {datapack:"MagentoEse_VerticalDataAuto"}) {
        job_id
      }
    }

*example: remote data pack*

	mutation {
      createDataInstallerJob(input: {datapack:"https://github.com/PMET-public/thisdoesntexist/archive/refs/heads/master.zip",is_remote:true,auth_token:"12345abcdef"}) {
        job_id
      }
    }
*returns:*

	{
  	"data": {
    	"createDataInstallerJob": {
      	"job_id": "0cdf3faf-1056-4f8c-98ed-96fde7ee3be7"
    	}
  	}
	}

**dataInstallerJobStatus**: Retrieves the current status of the provided `job_id` . Status includes `NOT_STARTED`,`IN_PROGRESS`, `FINISHED_SUCCESSFULLY`,`FINISHED_WITH_FAILURE` and `UNKNOWN` 

*example:*

	query{
		dataInstallerJobStatus(jobId: "c3f070cd-047e-40d6-b1b4-e6f726162f67") {
			job_status
			job_status_text
		}
	}
*returns:*

	"data": {
		"dataInstallerJobStatus": {
			"job_status": "3",
			"job_status_text": "FINISHED_WITH_FAILURE"
		}
	}

**dataInstallerLogs**: Retrives information about a data pack install which is displayed in the terminal with a CLI install. Can be selected by `job_id` in the case of a scheduled install, or by the name of a data pack.  Name can be partial string.

*example:*

	query{
  		dataInstallerLogs(jobId: "0cdf3faf-1056-4f8c-98ed-96fde7ee3be7") {
    	log_records {
      	add_date
      	datapack
      	job_id
      	level
      	message
    	}
  	}
	}
*returns:*

	{
	  "data": {
		"dataInstallerLogs": {
		  "log_records": [
			{
			  "add_date": "2022-02-25 15:24:46",
			  "datapack": "MagentoEse_VerticalDataAuto",
			  "job_id": "0cdf3faf-1056-4f8c-98ed-96fde7ee3be7",
			  "level": "info",
			  "message": "Copying Media"
			},
			{
			  "add_date": "2022-02-25 15:24:47",
			  "datapack": "MagentoEse_VerticalDataAuto",
			  "job_id": "0cdf3faf-1056-4f8c-98ed-96fde7ee3be7",
			  "level": "info",
			  "message": "Loading Sites, Stores and Views"
			},
			.....

## Exporting Data

These queries are written to return data in a format that can be saved as a file to be used by the Data Installer. There is a combination of extensions to native queries along with some that are custom. 
Magento GraphQL uses the store scope, so the queries are limited to the store scope as defined in the request header. Another limitation is that some queries will only return Active items.  You will not be able to export any Pages, Blocks, Categories, etc. that are set as Inactive

Some queries will have a `requires` node. This is to provide information about what other data elements may be needed by the data you are retrieving.  It will return the type (block,customer_segment,product, etc.) along with name, id and indetifier if appropriate. For example, if you are retrieving a page that contains one or more blocks, the query will return those details so you can include them in your blocks query.
Example:

	"requires": [
	{
		"id": 2,
		"identifier": test-block-2,
		"name": "test2",
		"type": "block"
	},
	{
		"id": 3,
		"identifier": "product-block",
		"name": "Product Block",
		"type": "block"
	}
	]

**storeConfig**: Use to create the `stores.json` file. The value of `fallback_theme` will be the same as `theme`, so it will have to be changed if needed.

    query {
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

**storeConfigurations**: Use to create the `store_configurations.json` file.  `additionalSettings` is optional. This would include the `path` from `core_config_data` of settings not included in the default list.
Configurations are retrieved from the store view scope and uses the same inheritance as the configuration. For example, if you do not have `Welcome Text` set at your Store View scope, it will return the value set for Website or Default. If a setting has no value set that path will not be included in the query results.
It is important to note that at this time only the Store View scope is used. There are many options that are set at the Website scope. Those would be retrieved, but then interpreted as Store View scope, so therefore would be imported but not used.  If you need to use other scopes, or a mixture of scopes, you should supplement this file with the manual .csv configuration files available

**Default Settings Included**
| Setting  | Path  |
| ------------ | ------------ |
| Stores->Configuration->Catalog->Catalog->Date & Time Custom Options->Use JavaScript Calendar  | catalog/custom_options/use_calendar  |
| Content->Configuration->HTML Head->Scripts & Style Sheets  | design/head/includes  |
| Content->Configuration->HTML Head->Favicon  | design/head/shortcut_icon  |
| Content->Configuration->Header->Logo Image  | design/header/logo_src |
| Content->Configuration->Header->Welcome Text  | design/header/welcome  |
| Stores->Configuration->General->General->Store Information->Store Name  | general/store_information/name |
| Stores->Configuration->General->Web->Default Pages->CMS Home Page  | web/default/cms_home_page |

    query {
		storeConfigurations(additionalSettings:["design/theme/theme_id"])  {
		items {
			path
			scope
			scope_code
			value
		}
		}
	}

**storeList**: Retrieves a list of stores. Not needed for a data pack, but could be used to drive UI selection. Group equates to Store in the UI, Store equates to View

	query{
		storeList {
			items {
				store_code
				store_name
				group_code
				group_name
				website_code
				website_name
			}
		}
	}


**categories**: Use to create the `categories.json` file. For the complete list of filtering options, see the GraphQL DevDocs.

    query{
    	categories(filters: {  parent_id: {in: ["3","4","8"]}} pageSize:50) {
    	items {
    		id
    		store_view_code
    		original_name:name
    		name:category_name
    		url_key
    		url_path
    		id_path:path
    		path:name_path
    		include_in_menu
			is_active
    		is_anchor
    		position
    		landing_page_id:landing_page
    		landing_page:landing_page_identifier
    		description_org:description
			description:description_import_content
    		display_mode
    		meta_description
    		meta_keywords
    		meta_title
    		page_layout
    		custom_design_id:custom_design
    		custom_design:custom_design_theme
			requires{
				id
				identifier
				name
				type
				}
    		}
    	}
    }

**cmsBlocks**: Use to create the `blocks.json` file. Include the block identifiers or Ids you want to include in the export.

    query{
    	cmsBlocks(identifiers: ["ac_locations","ac_offers"]) {
    		items {
    			store_view_code
    			title
    			identifier
    			content:block_content
				requires{
					id
					identifier
					name
					type
				}
    		}
    	}
    }

**cmsBlocksList**: Returns a list of all CMS Blocks. Not needed for a data pack, but used to drive a UI selection list. All data that is returned from `cmsBlocks` query can be returned

    query{
    	cmsBlocksList{
    		items {
    			store_view_code
    			title
    			identifier
    			block_id
    		}
    	}
    }

**cmsPages**: Use to create the `pages.json` file. Include the page identifiers or Ids you want to include in the export.If you need a list of all pages, set the input to an empty string `identifiers: [""]`. `page_id` is also available, but not necessary for a data pack

	query{
  		cmsPages(identifiers: ["anais-clement-quiz-p4","anais-clement-quiz-p3"]){
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
					requires{
						id
						identifier
						name
						type
					}
    			}
  		}
	}

**customerExport**: Use to create the `customers.json` file. Creates the same data set as a native customer export. At this time, the only filter that is supported is a comma delimited list of email addresses.

	query{
		customerExport(email:"me@example.com") {
			data
		}
	}

**customerList**: Returns a list of all Customers. Not needed for a data pack, but used to drive a UI selection list. No filtering is currently supported

	query{
		customerList {
			items {
			customer_id
			email
			firstname
			lastname
			}
		}
	}

**customerAddressExport**: Use to create the `customer_addresses.json` file. Creates the same data set as a native customer address export. At this time no filters are supported.

	query{
		customerAddressExport{
			data
		}
	}

**customerGroups**: Use to create the `customer_groups.json` file. Include the group names or Ids you want to include in the export. If you need a list of all customer groups other than the ones installed by default, set the input to an empty string `identifiers: [""]`. `group_id` is also available, but not necessary for a data pack

	query{
  		customerGroups(identifiers: ["VIP"]){
    			items {
      				name
      				tax_class
    			}
  		}
	}

**customerSegments**: Use to create the `customer_segments.json` file. Include the segment names or Ids you want to include in the export. If you need a list of all segments, set the input to an empty string `identifiers: [""]`. `segment_id` is also available, but not necessary for a data pack

	query{
  		customerSegments(identifiers: ["1","2","3","4"]){
    			items {
      				name
      				apply_to
      				conditions_serialized
      				description
      				site_code
					requires{
						id
						identifier
						name
						type
					}
    			}
  		}
	}

**cartRules**: Use to create the `cart_rules.json` file. Include the cart rule names or Ids you want to include in the export. If you need a list of all cart rules, set the input to an empty string `identifiers: [""]`. `rule_id` is also available, but not necessary for a data pack

	query{
		cartRules(identifiers: ["1","2","3","4","5"]) {
			items {
				actions_serialized
				apply_to_shipping
				conditions_serialized
				coupon_code
				coupon_type
				customer_group
				description
				discount_amount
				discount_qty
				discount_step
				is_advanced
				is_rss
				name
				reward_points_delta
				simple_action
				simple_free_shipping
				site_code
				sort_order
				stop_rules_processing
				times_used
				use_auto_generation
				uses_per_coupon
				uses_per_customer
				sort_by
				is_active
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}

**msiSource**: Use to create the `msi_source.json` file. Include the source codes you want to include in the export. If you need a list of all sources, set the input to an empty string `identifiers: [""]`. `default` source will not be included with this method

	query{
		msiSource(identifiers: ["ac_warehouse"]){
			items {
				city
				contact_name
				country_id
				description
				email
				enabled
				fax
				frontend_description
				frontend_name
				is_pickup_location_active
				latitude
				longitude
				name
				phone
				postcode
				region_id
				source_code
				street
				use_default_carrier_config
			}
		}
	}

**msiStock**: Use to create the `msi_stock.json` file. Include the stock names or Ids you want to include in the export. If you need a list of all stocks, set the input to an empty string `identifiers: [""]`.  `stock_id` is also available but not needed for a Data Pack

	query{
		msiStock(identifiers: ["2"]){
			items {
				site_code
				source_code
				stock_name
			}
		}
	}

**stockSourcesExport**: Use to create the `stock_sources.json` file that sets product inventory for each source. Include the source codes to include in the export. 

	query
		{
			stockSourcesExport(sourceCode: ["carvelo_trenton,carvelo_broadway,carvelo_long_island"]) {
				data
		}
	}

**customAttributeMetadata**: Use to create the `product_attributes.json` and `customer_attributes.json` files. Include attribute codes you want to include in the export along with the `entity_type` value of either `catalog_product` or `customer`. Note that the queries have some differences based on the information supported by each attribute type

***Product Attributes***

	query{
		customAttributeMetadata(
			attributes: [
				{
					attribute_code: "testswatch"
					entity_type: "catalog_product"
				}
				{
					attribute_code: "lipcarepottube"
					entity_type: "catalog_product"
				}
			]
		) {
			items {
				attribute_code
				store_view_code
				frontend_input:input_type
				attribute_options {
					value
					label
					swatch_value
				}
				storefront_properties {
					is_filterable:use_in_layered_navigation
					used_in_product_listing:use_in_product_listing
					is_filterable_in_search:use_in_search_results_layered_navigation
					is_visible_on_front:visible_on_catalog_pages
					position
				}
				admin_properties {
					additional_data
					attribute_set
					frontend_label
					is_comparable
					is_filterable_in_grid
					is_html_allowed_on_front
					is_pagebuilder_enabled
					is_required_in_admin_store
					is_searchable
					is_used_for_price_rules
					is_used_for_promo_rules
					is_used_in_grid
					is_visible
					is_visible_in_advanced_search
					is_visible_in_grid
					is_wysiwyg_enabled
					search_weight
					used_for_sort_by
				}
			}
		}
	}

***Customer Attributes***

	query{
		customAttributeMetadata(
			attributes: [
				{
					attribute_code: "vehicle_1"
					entity_type: "customer"
				}
				{
					attribute_code: "vehicle_1_mileage_range"
					entity_type: "customer"
				}
			]
		) {
			items {
				attribute_code
				store_view_code
				frontend_input:input_type
				attribute_options {
					label
				}
				admin_properties {
					is_visible
					frontend_label
					is_filterable_in_grid
					is_used_in_grid
					is_visible_in_grid
					is_required
					is_used_for_customer_segment
					sort_order
					used_in_forms
				}
			}
		}
	}



**attributeList**: Used to display user defined attributes for selection from a UI. The same query can be used across all attribute types, but only `customer` and `catalog_product` are currently supported by data installer. The `is_core` flag indicates attributes that are installed by the core code but are still classified as user defined and not system. It is likely that these attributes do not need to be exported unless they have been edited. This list will also return any attributes installed by extensions or sample data. For example the Luma sample data will include product attributes like `size`, `material` and `activity`. Unless those attributes have been edited or are used by the current data pack, it is likley they will not be need to be included.

	query{
		attributeList(entityType: "catalog_product") {
			items {
				attribute_code
				default_label
				scope
				is_core
			}
		}
	}

**rewardsPointsExchangeRate**: Use to create the `reward_exchange_rate.json` file. Include the Ids you want to include in the export. If you need a list of all exchange rates, set the input to an empty string `identifiers: [""]`. `rate_id` is also available, but not necessary for a data pack

	query{
		rewardsPointsExchangeRate(identifiers: ["1"]) {
			items {
				currency_amount
				customer_group
				direction
				points
				site_code
			}
		}
	}

**upsells**: Use to create the `upsells.json` file to populate Related Products Cross Sells and Upsells. Include the Ids or names you want to include in the export.  If you need a list of all related product rules, set the input to an empty string `identifiers: [""]`. `rule_id` is also available, but not necessary for a data pack

	query{
		upsells(identifiers: ["4","3"]) {
			items {
				actions_serialized
				apply_to
				conditions_serialized
				customer_segments
				name
				sort_order
				positions_limit
				is_active
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}

**widgets**: Use to create the `widgets.json` file to populate Widgets. Include the Ids or names you want to include in the export. If you need a list of all widgets, set the input to an empty string `identifiers: [""]`. `widget_id` is also available but not necessary for a data pack. `ui_type` is also available if a UI friendly version of type needs to be displayed. Note, only single layout update is supported. If multiple layout updates are defined, only the first is exported.

	query{
		widgets(identifiers: ["1","2","3"]) {
			items {
				block_reference
				entities
				instance_type
				layout_handle
				page_for
				page_group
				page_template
				sort_order
				store_view_code
				theme
				title
				widget_parameters
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}


**catalogRules**: Use to create the `catalog_rules.json` file. Include the catalog rule names or Ids you want to include in the export.  If you need a list of all catalog rules, set the input to an empty string `identifiers: [""]`. `rule_id` is also available, but not necessary for a data pack

	query{
		catalogRules(identifiers: ["Test Rule"]) {
			items {
				actions_serialized
				conditions_serialized
				customer_groups
				description
				discount_amount
				dynamic_blocks
				name
				simple_action
				site_code
				sort_order
				stop_rules_processing
				is_active
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}

**adminRoles**: Use to create the `admin_roles.json` file. Include the role names or Ids you want to include in the export. If you need a list of all roles, set the input to an empty string `identifiers: [""]`.  This will also only return a list of distinct roles and not resources. Default roles of `admin` and `Administrator` are not included. `role_id` can also be included in the items node, but it not necessary for a Data pack.

	query{
		adminRoles(identifiers: ["Sales Admin"]) {
			items {
				role
				resource_id
			}
		}
	}

**adminUsers**: Use to create the `admin_users.json` file. Include the admin user names or Ids you want to include in the export. As passwords cannot be decoded, `password` will return a static value of `Password1`.  If you need a list of all users, set the input to an empty string `identifiers: [""]`. Default `admin` user is not included. `user_id` can also be included in the items node, but it not necessary for a Data pack.

	query{
		adminUsers(identifiers: ["2"]) {
			items {
				email
				firstname
				lastname
				password
				role
				username
			}
		}
	}

**dataInstallerStoreSettings**: Use to create the `settings.json` file.

***Basic Query***

	query{
		dataInstallerStoreSettings {
			store_code
			store_view_code
			site_code
		}
	}
***Advanced Query***
*Unless you are overriding the default settings, do not include the arguments. Including them in the query return is also optional unless you are overriding them.*

	query{
		dataInstallerStoreSettings(
		restrictProductsFromViews: "N",
		productImageImportDirectory : "pub/media/import",
		productValidationStrategy: "validation-stop-on-errors"
		) {
			store_code
			store_view_code
			site_code
			product_validation_strategy
		}
	}


**Gift Cards**: Used to create the `gift_cards.json` file. When Gift Cards are imported along with other products, the row will be skipped with an error. Gift cards need to be imported separatly. The same `productExport` query is used, just restricted to only Gift Cards. There is no issue if the Gift Cards are also included in the main products file as they will be skipped during import.

	query{
		productExport(categoryIds:"41") {
			data
		}
	}


**dynamicBlocksExport**: Use to create the `dynamic_blocks.json` file. Include the Dynamic Block names or Ids you want to include in the export. This is different than the core *dynamicBlocks* query. It does use the same DynamicBlock Type, but it will not return all the information that the core query returns. If you need a list of all dynamic blocks, set the input to an empty string `identifiers: [""]`. `banner_id` is also available but not necessary for a data pack. `ui_type` is also available if a UI friendly version of type needs to be displayed

	query{
		dynamicBlocksExport(identifiers: ["1","2"]) {
			items {
				banner_content
				name
				segments
				store_view_code
				type
				is_enabled
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}


**productExport**: Use to create the `products.json` file. Creates the same data set as a native product export. At this time, the only product filter that is supported is a list of category ids to export products from.

	query{
		productExport(categoryIds:"47,46") {
			data
		}
	}


**advancedPricingExport**: Use to create the `advanced_pricing.json` file. Creates the same data set as a native advanced pricing export. At this time, the only product filter that is supported is a list of category ids to export products from.

	query{
		advancedPricingExport(categoryIds:"47,46") {
			data
		}
	}


**reviews (products)**: Use to create the `reviews.json` file. The product query contains the data necessary to populate product reviews. So, there is no unique reveiws query. Search and filter functionality follows the documented features of the products query

	query{
		products(
		filter: {
			sku:{
				in:["nailpolishcollection","mensgroomingkit"]
			}
		}
		pageSize: 200
		currentPage: 1
		) {
			items{
			sku
			store_view_code
			reviews{
				items{
					summary
					text
					nickname
					ratings_breakdown{
						name
						value
					}
				}
			}
			}
 		}
	}

**pageBuilderTemplates**: Use to create the `templates.json` file, to import Page Builder Templates. Include the template names or Ids you want to include in the export. If you need a list of all templates, set the input to an empty string `identifiers: [""]`. `template_id` is also available, but not necessary for a data pack

	query{
		pageBuilderTemplates(identifiers: ["1","Heading / Video"]) {
			items {
				content
				created_for
				name
				preview_image
				requires{
					id
					identifier
					name
					type
				}
			}
		}
	}

**imagesExtract**: creates a downloadable, Data Installer compatible .zip archive of all images contained in the given elements. `categoryIds` is the list of categories containing the products to retrieve media from (likely the identical list from the  `productExport` query). `cmsDir`` is optional: Value is single directory under media\\wysiwyg to extract images from. Only include if you need to extract additional images not included in the existing blocks, pages, etc. If left empty all wysiwyg images will be extracted.

	query{
		imagesExtract(
			blockIds: "83, 84, 85"
			categoryIds: "214"
			dynamicBlockIds:"58, 91"
			pageIds:"1"
			templateIds:"25, 26, 27"
			cmsDir:"template"
			){
		zip_file_download
		zip_file_server_path
		}
	}
An optional `all_iamges` node can be included if you want details on all of the contained images. This could be used for retrieving the images remotely rather than downloading the .zip file. `source` is the absolute path of the image on the server. `in_datapack` is the path to save the image in a Data pack. `image_url` is the url to the image. This does not include any images that were extracted under the `cmsDir` argument.	
	
	all_images{
		source
		in_datapack
		image_url
    }

**companies**: Use to create the `b2b_companies.json` file.
Include the company names or Ids you want to include in the export. If you need a list of all companies, set the input to an empty string `identifiers: [""]`.

	query{
		companies(identifiers: ["1"]) {
			items {
				site_code
				company_id
				legal_name
				company_name:name
				company_email:email
				address:legal_address {
					city
					country_id:country_code
					postcode
					region{
						region:region_code
					}
					street
					telephone
				}
				reseller_id
				vat_tax_id
				
				company_admin{
					email
				}
				approval_rules{
					items{
						name
						description
						is_active
						apply_to_roles
						conditions_serialized
						approval_from
						requires_admin_approval
						requires_manager_approval
						applies_to_all
						created_by
					}
				}
				shared_catalog{
					name
					description
					type
					categories{
						path
					}
				}
				credit_export{
					credit_limit{
						value
					}
				}
				sales_representative{
					firstname
					lastname
					email
					username
					role
					password:placeholder_password
				}
					users_export(filter: {}, pageSize: 20, currentPage: 1) {
					items{
						firstname
						lastname
						email
						password:placeholder_password
						add_to_autofill
						addresses{
							street
							city
							region{
								region
							}
							postcode
							telephone
							country_id:country_code
						}
						role{
							name
						}
						team{
							name
						}
						requisition_lists_export(pageSize: 20, currentPage: 1){
							items{
								name
								description
									items{
										items{
											product{
												sku
                							}
                						quantity
              							}
            						}
          						}
        					}
      					}
    				}
    				roles_export{
						items{
							name
							permissions{
								id
							children{
								id
								children{
									id
								}
							}
						}
        
					}
				}
			}
		}
	publicSharedCatalog{
		name
		description
		type
		categories{
			path
		}
		}
	}

