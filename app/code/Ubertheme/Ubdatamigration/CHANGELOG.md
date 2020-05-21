3.2.0
=====
* Fix and Improvement:
    - Supported convert the sales hidden tax data fields (in sales data section)
    (https://www.ubertheme.com/question/hidden-item-tax-amount-is-not-migrating/)
    - Added more CLI command to clean mapping data/log data in all steps:
        + php -f bin/ubdatamigration clean
        + php -f bin/ubdatamigration clean --step=2
            + step values: 2,3,4,5,6,7,8
    - Supported migrate the active Sales Quotes data (The sales quotes which has not convert to sales order yet)
    - Added more code to process delta on migrated items which has changed after the first migration.
    and delta on newly added data items:
        + catalog_category_entity and related child data
        + catalog_product_entity and related child data
        + customer_entity and related child data
        + sales_flat_order and related child data
    - Fixed to compatible with Magento CE ver.2.3.2
    - Updated readme.html
    
3.1.9
=====
* Fix and Improvement:
    - Fixed to handle more specials cases:
        https://www.ubertheme.com/question/error-with-unescaped-database-query/
        https://www.ubertheme.com/question/mage2ratingoptionvote-remote-ip-cannot-be-blank/
        https://www.ubertheme.com/question/option-type-swatch-is-being-imported-on-product/
        https://www.ubertheme.com/question/migrating-customers-causes-error/
        https://www.ubertheme.com/question/error-message-while-importing-customers/
    - Updated readme.html
    - Fixed to compatible with Magento CE ver.2.3.1

3.1.8
=====
* Fix and Improvement:
    - Fixed to handle more specials cases:
        https://www.ubertheme.com/question/step-3-error-integrity-constraint-violation-1062/
        https://www.ubertheme.com/question/receiving-a-4200-sql-error-while-migrating-product/
        https://www.ubertheme.com/question/ub-tools-library-with-magento2-component-type/
        
3.1.7
=====
* Fix and Improvement:
    - Fixed to handle more specials cases of data
    - Allowed convert configurable product's pricing: special price and prices of variations
    
3.1.6
=====
* Fix and Improvement:
    - Fixed to handle more specials cases of data:
        + https://www.ubertheme.com/question/how-to-fix-mage2customerentitydecimal-error/ (but in comment for take performance)
        + https://www.ubertheme.com/question/error-importing-categories-from-magento-1-9-3-8/ (but in comment for take performance)
        + https://www.ubertheme.com/question/ub-data-migration-pro-install-problem/
    + Allowed delta migrate for sub categories of migrated category
    + Tuning to allow use PHP memcached on PHP7
    + Fixed and Tested compatibility with M2.2.6

3.1.5
=====
* Fix and Improvement:
    - Tested to compatible with Magento CE 2.2.5
    - Fixed to handle more specials cases of data:
        - https://www.ubertheme.com/question/delta-migration-step-7-error/
        - https://www.ubertheme.com/question/getting-a-404-page-in-admin/
        - https://www.ubertheme.com/question/migrated-category-does-not-show-all-products-2/
        - https://www.ubertheme.com/question/will-reset-button-remove-all-the-migrated-products/
        - https://www.ubertheme.com/question/file-cached/
        
3.1.4
=====
* Fix and Improvement:
    - Fixed to compatible with Magento CE 2.2.4
    - Fixed to handle more specials cases of data and server enviroment:
        - https://www.ubertheme.com/question/migrated-products/
        - https://www.ubertheme.com/question/ub-data-migration-pro-not-working-properly/
        
3.1.3
=====
* Fix and Improvement:
    - Fixed to handle more specials cases of data and server enviroment:
        - https://www.ubertheme.com/question/notice-undefined-index-host-error-on-aws/
        - https://www.ubertheme.com/question/google-cloud-info/
        - https://www.ubertheme.com/question/sql-error-2/ 
        - https://www.ubertheme.com/question/error-when-migrating-products/ 
        - https://www.ubertheme.com/question/error-when-migrating-products-2/

3.1.2
=====
* Fix and Improvement:
    - Tested and fixed to compatible with Magento ver.2.2.2
    - Fixed to handle more specials cases of data
    
3.1.1
=====
* Fix and Improvement:
    - Tested and fixed to compatible with Magento ver.2.2.0
    - Fixed issue with delta migration mode in step #7 in ver.3.1.0: https://www.ubertheme.com/question/error-at-start-step-7/ 

3.1.0
=====
* Fix and Improvement:
    * Allow remain original primary IDs on data objects:
    - Step #5: Products Migration
          - Catalog Products Entities
          - Catalog Product Galleries
          - Catalog Product Options
          - Catalog Product Option Type Values
          - Catalog Product Option Type Titles
          - Catalog Product Option Type Prices
          - Catalog Product Option Prices
          - Catalog Product Option Titles
          - Catalog Product Stock Items
          - Catalog Product Links
          - Catalog Product Super Links
          - Catalog Product Super Attributes
          - Catalog Product Bundle Options
          - Catalog Product Bundle Selections
          - Catalog Product Download Links
          - Catalog Product Download Samples
    - Step #6: Customers Migration
        - Customers Entities
        - Customer EAV Data Tables:
            - `customer_entity_datetime`
            - `customer_entity_decimal`
            - `customer_entity_int`
            - `customer_entity_text`
            - `customer_entity_varchar`
        - Customer Addresses
        - Customer Address EAV Data Tables:
            - `customer_address_entity_datetime`
            - `customer_address_entity_decimal`
            - `customer_address_entity_int`
            - `customer_address_entity_text`
            - `customer_address_entity_varchar`
    - Step #7: Sales data migration
        - Sales Rules
        - Sales Rule Coupons
        - Sales Orders
        - Sales Order Items
        - Sales Order Addresses
        - Sales Quotes
        - Sales Quote Items
        - Sales Quote Item Options
        - Sales Quote Addresses
        - Sales Quote Address Items
        - Sales Quote Shipping Rates
        - Sales Payments
        - Sales Payment Transactions
        - Sales Invoices
        - Sales Shipment
        - Sales Shipment Track
        - Sales Shipment Item
        - Sales Shipment Comment
        - Sales Credit Memos
        - Sales Order Taxes
        - Sales Order Tax Items
    * Allow migrate Custom Attributes on Customer and Customer Address
    * Changed new UI,UX in all steps
    * Upgraded Yii core: ver.1.1.16 -> ver.1.1.19
    * Compatible with Magento ver.2.1.9
    * Fixed: applied tweaks for some special database in the sales data section

3.0.9
=====
* Fix and Improvement:
    * Allow setting to merge default website/store/store view in step #2 (migrate websites/stores/store views)
    * Allow delta migrate update for migrated items in all steps. (not only migrate for new added items in M1)
    * Auto re-structure Product Attribute Sets after migrated.
    * Fixed: applied tweaks for some special database in the sales data section
        
3.0.8
=====
* Fix and Improvement:
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/error-when-running-migrate-products/
        - https://www.ubertheme.com/question/shipping-name-is-too-long/
    
3.0.7
=====
* Fix and Improvement:
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/getting-error-on-import-products-in-ub-migrate-pro/
    * Allowed migrate Downloadable Links Purchased data
    * Tested compatible with Magento CE 2.1.6
        
3.0.6
=====
* Fix and Improvement:
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/error-order-status-cannot-be-blank/
        - https://www.ubertheme.com/question/migration-fails-at-step-5/
        - https://www.ubertheme.com/question/product-multi-select-items-randomized/
        
3.0.5
=====
* Fix and Improvement:
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/conversion-vat-not-correct/
        
3.0.4
=====
* Fix and Improvement:
    * Upgraded to compatible with Magento v2.1.3    
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/error-message-step-7-sales-data-transfer/
        - https://www.ubertheme.com/question/customer-suffix-is-too-long/
        - https://www.ubertheme.com/question/fatal-error-in-migrating-sales-orders/

3.0.3
=====
* Fix and Improvement:
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/upgrade-ub-data-migration-pro-2-0-6-to-3-0-2/
        - https://www.ubertheme.com/question/get-an-error-on-migration/
        - https://www.ubertheme.com/question/cant-select-attributes/
        - https://www.ubertheme.com/question/sql-errors-on-steps-7-and-8-of-data-migration/
        
3.0.2
=====
* Fix and Improvement:
    * (Fine-tuning): Notification improvement for Command-line interface
    * (Fine-tuning): rename the log file to 'ub_data_migration.log'
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/upgrading-ub-data-migration-pro-error/
        - https://www.ubertheme.com/question/migration-tool-pro-sales-orders/
        - https://www.ubertheme.com/question/sales-import-not-completing/
        
3.0.1
=====
* Fix and Improvement:
    * Tested compatible with Magento CE 2.1.2
    * Supported CLI commands:  After done needed settings for each step, Users can migrate data by run command lines in CLI mode. (Solved issue #4)
    * Handle some special case: 
        * https://www.ubertheme.com/question/compatibility-2/
        * https://www.ubertheme.com/question/cdbexception-at-step-1/
        * https://www.ubertheme.com/question/i-am-getting-an-error-while-importing-products/
    
3.0.0
=====
* Improve solution to migrate data:
    * Support incremental data migration
    * Improvement UI/UX and performance of data migration: Only with 8 steps to migrate your key data objects.
    * Used ajax requests in all steps of data migration 
    * Solved all issues in versions 2.x: Security issue...
    * Don't required high performance web server to migrate data, Easy migrate data with big volume.
    * Don't use SQLite from versions 3.x

2.0.6
=====
* Fix issues: 
    * https://www.ubertheme.com/question/getting-errors-after-data-migration/
* Tuning to improve performance: 
    * Improve way to migrate attribute sets, attribute groups. (step 3)
    * Improve categories listing in form (step 4)
    * Improve and tuning Products Stock data migration (step 5)
* Handle for all redirect types of Categories and Products Rewrite Urls: No redirect, 301, 302 (step 4, step 5)
* Tested compatible with Magento CE 2.1.1

2.0.5
=====
* Fix issues: 
    * https://www.ubertheme.com/question/error-while-migrating-sales/
    * https://www.ubertheme.com/question/getting-error-in-migrating-customers/
    * https://www.ubertheme.com/question/404-page/
     
2.0.4
=====
* Allow migrate custom product tax classes
* Fixed issue not found when access this tool in back-end after install module successfully in some case. 

2.0.3
=====
* Upgrade compatible with CE 2.1.0, some tables was change data structure: (Compared CE 2.1.0 vs CE 2.0.x)
    * `eav_attribute`: http://i.prntscr.com/7c14c90a6ace46e39accdb4020d1db89.png (initial attributes with fresh installation)
    * `eav_attribute_group`: http://i.prntscr.com/1899719ba3c245468b5cc86c81a8c4b5.png
    * `eav_entity_attribute`: http://i.prntscr.com/adda815076c346b0b8b080c28ca4a64c.png
    * `catalog_category_product`: http://i.prntscr.com/44187be28d784930ad4f8b30cf68e566.png
    * `catalogrule`: http://i.prntscr.com/f5985929f291424f9803b578d058a19f.png
    * `catalogrule_product`: http://i.prntscr.com/cef4fb6b5997477fa850be99cbcd3892.png
    * `customer_entity`: http://i.prntscr.com/b520b54d3dbd41359803189a59e49f11.png
    * `sales_invoice_grid`: http://i.prntscr.com/97e8379ef3a44dd28409915857169b1c.png
    * Some tables in sales data structure was remove CONSTRAINT:
          * `sales_bestsellers_aggregated_daily, sales_bestsellers_aggregated_monthly, sales_bestsellers_aggregated_yearly`: http://i.prntscr.com/70d0d47dcd2147e4be956420409ed012.png
  
2.0.2
=====
* Improvement and fixed bugs:
    * Tuning to support Nginx server.
    * Fixed bugs:https://www.ubertheme.com/question/ub-dm-pro-error/
    
2.0.1
=====
* Improvement and fixed bugs:
    * Fixed bugs: 
        * Issue #1: https://bitbucket.org/joomsolutions/ub-module-ubdatamigration-pro/issues/1/issue-with-sales_order_status_label-model
    * Allow convert `group_price` data to `tier_price` data
        * Issue: #2: https://bitbucket.org/joomsolutions/ub-module-ubdatamigration-pro/issues/2/issue-with-group-price
    * Tuning and improve performance
    * Tested compatible with Magento CE 2.0.6, CE 2.0.7

2.0.0
=====
* First released
