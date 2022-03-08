# Magento 2 Data Install Module GraphQl

This module provides GraphQL support for the Data Install Module. There are two sets of functionality.

1. Data Installer functions like launching a data pack import and retrieving log information about an import
2. Exporting of data via a GraphQl query to be used in a data pack

Details on query arguments and types are available in the online GraphQL docs. 

## Data Installer Support

**createDataInstallerJob**: Similiar to installing a data pack via CLI. This will schedule a data pack import, and return the `job_id` of the process

*example:*

	mutation {
      createDataInstallerJob(input: {datapack:"MagentoEse_VerticalDataAuto"}) {
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
Magento GraphQL uses the store scope, so the queries are limited to the store scope as defined in the request header. Another limitation is the query will only return Active items.  You will not be able to export any Pages, Blocks, etc. that are set as Inactive

**storeConfig**: Use to create the `stores.json` file

    query {
    	storeConfig {
    		is_default_view:is_default_store
    		is_default_store:is_default_store_group
    		root_category_id
    		root_category_uid
    		store_view_code:store_code
    		store_code:store_group_code
    		store_name:store_group_name
    		store_view_name:store_name
    		view_order:store_sort_order
    		site_code:website_code
    		site_name:website_name
    		store_root_category:root_cateogry_name
    		theme
    	}
    }

**categories**: Use to create the `categories.json` file. Include the ids of the categories you want to include in the export

    query{
    	categories(filters: { ids: {in: ["4","5","6","7","8"]}} pageSize:50) {
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
    		is_anchor
    		position
    		landing_page_id:landing_page
    		landing_page:landing_page_identifier
    		description
    		display_mode
    		meta_description
    		meta_keywords
    		meta_title
    		page_layout
    		custom_design_id:custom_design
    		custom_design:custom_design_theme
    		}
    	}
    }

**cmsBlocks**: Use to create the `blocks.json` file. Include the block identifiers or Ids you want to include in the export. `content` will include the raw content, so any Page builder id substitutions will need to be done manually as outlined in the Data Installer documentation https://github.com/PMET-public/module-data-install#content-substitution

    query{
    	cmsBlocks(identifiers: ["ac_locations","ac_offers"]) {
    		items {
    			store_view_code
    			title
    			identifier
    			content:block_content
    		}
    	}
    }

**cmsPages**: Use to create the `pages.json` file. Include the page identifiers or Ids you want to include in the export. `content` will include the raw content, so any Page builder id substitutions will need to be done manually as outlined in the Data Installer documentation https://github.com/PMET-public/module-data-install#content-substitution

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
    			}
  		}
	}

**customerGroups**: Use to create the `customer_groups.json` file. Include the group names or Ids you want to include in the export. 

	query{
  		customerGroups(identifiers: ["VIP"]){
    			items {
      				name
      				tax_class
    			}
  		}
	}

**customerSegments**: Use to create the `customer_segments.json` file. Include the segment names or Ids you want to include in the export. `conditions_serialized` will include the raw content, so any id substitutions will need to be done manually as outlined in the Data Installer documentation https://github.com/PMET-public/module-data-install#content-substitution

	query{
  		customerSegments(identifiers: ["1","2","3","4"]){
    			items {
      				name
      				apply_to
      				conditions_serialized
      				description
      				site_code
    			}
  		}
	}

**cartRules**: Use to create the `cart_rules.json` file. Include the cart rule names or Ids you want to include in the export. `conditions_serialized` and `actions_serialized` will include the raw content, so any id substitutions will need to be done manually as outlined in the Data Installer documentation https://github.com/PMET-public/module-data-install#content-substitution

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
			}
		}
	}

**msiSource**: Use to create the `msi_source.json` file. Include the source codes you want to include in the export. 

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

**msiStock**: Use to create the `msi_stock.json` file. Include the stock names or Ids you want to include in the export. 

	query{
		msiStock(identifiers: ["2"]){
			items {
				site_code
				source_code
				stock_name
			}
		}
	}

**customAttributeMetadata**: Use to create the `product_attributes.json` file. Include attribute codes you want to include in the export. 

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

**rewardsPointsExchangeRate**: Use to create the `reward_exchange_rate.json` file. Include the Ids you want to include in the export. 

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
