# Module UB Data Migration Professional
> This is a Magento 2 module. This tool allows migrate some key data objects from Magento 1.x to Magento 2.x

### Author: [UberTheme](http://www.ubertheme.com)

### Allow Features:
- [x] Migrate Websites, Stores, Store Views
- [x] Migrate Product Attribute Sets, Product Attribute Groups, Product Attributes
- [x] Migrate Catalog Categories
- [x] Migrate Catalog Products
- [x] Migrate Customer Groups and Customers
- [x] Migrate Sales Data:
    + Sales Rules & Coupons (Cart Price Rules)
    + Sales Order Statuses
    + Sales Orders: _With the following sales data objects, our tool only migrates items related to Sales Orders:_
        + Sales Quote
        + Sales Payments
        + Sales Invoices
        + Sales Shipments 
        + Sales Credit Memo
    + Sales Aggregated Data:
        + Sales Order Aggregated Created
        + Sales Order Aggregated Updated
        + Sales Refunded Aggregated 
        + Sales Refunded Aggregated Order 
        + Sales Invoiced Aggregated
        + Sales Invoiced Aggregated Order
        + Sales Shipping Aggregated 
        + Sales Shipping Aggregated Order 
        + Sales Bestsellers Aggregated Daily 
        + Sales Bestsellers Aggregated Monthly 
        + Sales Bestsellers Aggregated Yearly 
- [x] Migrate Product Reviews, Ratings data
- [x] Migrate Tax Rules, Tax Zones and Tax Rates
- [x] Migrate Product Tier Prices
- [x] Migrate Product Group Prices (will be convert to tier price)
- [x] Tax data
    + Tax classes
    + Tax Calculation Rate
    + Tax Calculation Rules 
    + Tax Order Aggregated Created 
    + Tax Order Aggregated Updated
- [x] Catalog Price Rules
- [x] Email Templates and Newsletter Data
    + Email Templates
    + Newsletter Subscribers
    + Newsletter Templates
    + Newsletter Queues
    + Newsletter Problem Reports
- [x] Migrate System Increment IDs (EAV Entity Store)

### Compatiblity:
    + Magento CE 1.x: 1.6.x, 1.7.x, 1.8.x, 1.9.x
    + Magento CE 2.0.0 and later

### Installation Preconditions:
- Install a Magento 2 website:
    + Download Magento Community Edition 2.x from the link: https://www.magentocommerce.com/download
    + Follow [Installation guide](http://devdocs.magento.com/guides/v2.0/install-gde/install-quick-ref.html) to install a Magento 2 website
- Once the Magento 2 installation is completed, please backup or dump your Magento 2 database for backup purpose
- Don't activate Magento 2 cron jobs
- Assign Write permission to the `pub` folder at`PATH_TO_YOUR_MAGENTO_2/pub`

### How To Install:

#### Option 1: Install from zip file downloaded (manual install) 
- Extract the zip file you have downloaded. Copy the `app` folder and paste to root folder of your Magento 2 (_you have to select merge folders/files option_).
- Open your Terminal window, go to your Magento 2 folder and run below commands to install this module:
    + `php -f bin/magento module:enable -c Ubertheme_Ubdatamigration`
    + `php -f bin/magento setup:upgrade`
    + `php -f bin/magento cache:clean` (_optional_)

#### Option 2: Install via Composer:
- Open your terminal window, go to your Magento 2 folder and run below command:
    + `composer config repositories.ubdatamigration-pro vcs REPOSITORY_URL_OF_THIS_MODULE`
    + `composer require ubertheme/module-ubdatamigration-pro`
    + `php -f bin/magento module:enable -c Ubertheme_Ubdatamigration`
    + `php -f bin/magento setup:upgrade`
    + `php -f bin/magento cache:clean` (_optional_)

#### Enable PHP Memcached to improve the performance of the UB Data Migration Pro tool
    + Default settings of our tool uses cached file storeage. We highly recommend you enable the PHP memcached to improve the overall performance. 
    + First, make sure you have the PHP memcached installed in your web server.
      (Example: https://www.digitalocean.com/community/tutorials/how-to-install-and-use-memcache-on-ubuntu-14-04)
    + Once done, please enable to use php memcached with our migration tool:
        + Supposed you already install our migration tool successfully, open the configuration file at: `/pub/ub-tool/protected/config/main.php`
         and navigate to the lines: 47 -> 65
         and comment from lines 63 -> 65
         and un-comment the lines: 53 -> 61

#### For Nginx Server only:
 + After install this tool. Let's add more below config line to your nginx.conf:
 `include path_to_your_magento2/pub/ub-tool/nginx.conf;`
 
     + Example content in your nginx.conf:

    ```
    server {
    
        listen 80;
        
        #Other configs ....
        
        #include config file for ub-tool
        include path_to_your_magento2/pub/ub-tool/nginx.conf;
     }
     
    ```
         
 + Restart your Nginx server.
 
### How To Use:

#### 1 - Prepare to migrate data:
- Set maintenance mode for your Magento 1
- Off all cron jobs related to your Magento 1 and Magento 2 sites

#### 2 - Login to back-end of your Magento 2, follow step by step with UI of our tool as below screen shots:
- Settings Steps:
- Step 1: Database settings
![step 1](http://i.prntscr.com/f5cc08c4597247159e5789378b76ba67.png)

- Step 2: Websites, Stores  settings
![step 2](http://i.prntscr.com/855bd542382c49e3bed0b989a06e1ce0.png)

- Step 3: Attributes settings 
![step 3](http://i.prntscr.com/2b5206f7bbdc4de6b99487407586f08c.png)

- Step 4: Catalog Categories settings
![step 4](http://i.prntscr.com/7c1fe56d77de4462b347d9f2448546ed.png)

- Step 5: Catalog Products settings
![step 5](http://i.prntscr.com/9f1739fd5e4e49efb34e6081ce53592d.png)

- Step 6: Customers data settings
![step 6](http://i.prntscr.com/40a5144988f548afb3fd78a2b0729307.png)

- Step 7: Sales Data settings
![step 7](http://i.prntscr.com/51be97e167874bb2af3dd93f4299175e.png)

- Step 8: Other data objects settings
![step 8](http://i.prntscr.com/0dec616e2efc4fd496af6eaa0c066db4.png)

- Migrate data Steps:
![steps migrate](http://i.prntscr.com/261582b74a2747fb92a5df20cbcf0493.png)

#### 3 - Finish (_required_)
**To finish the data migration from Magento 1.x to Magento 2.x, you have to do some tasks below:**

- Copy media files
    + Copy the folder at PATH_YOUR_MAGENTO_1\media\catalog and paste replace to PATH_YOUR_MAGENTO_2\pub\media\
    + Copy the folder at PATH_YOUR_MAGENTO_1\media\downloadable and paste replace to PATH_YOUR_MAGENTO_2\pub\media\
    + Make recursive write permission to "catalog" and "downloadable" folders which you have just copied.
- Re-Index the data: In your terminal window, go to your Magento 2 folder and run below commands:
    `php -f bin/magento cache:clean`
    `php -f bin/magento indexer:reindex`
- Clean Magento 2 cache: In your terminal window, go to your Magento 2 folder and run below commands:
    `php -f bin/magento cache:clean`
    `php -f bin/magento cache:flush`
- Upgrade Password Hash (__This is optional task for more security__): In your terminal window, go to your Magento 2 folder and run below command:
    `php -f bin/magento customer:hash:upgrade`
    
#### 4 - How to run delta migration to update new incremental data changes: 
    - Delta migration is a completely new feature in UB Data Migration Pro V3. It enables to migrate incremental changes since the last time you migrate new data like Customers, Sales orders... Simply follow steps below:
        + How To migrate new Customers: 
            + Navigate to 'UB Data Migration Pro' dashboard, click the `Re-run` button in step #6 (Migrate Customers).
            + Once done, Reindex your data using the command: `php -f bin/magento indexer:reindex`
            + Then clean M2 cache with the command: `php -f bin/magento cache:clean`
        + How to migrate new Sales Orders:
            + In your 'UB Data Migration Pro' dashboard, click the `Re-run` button in step #7 (Migrate Sales)
            + Clean M2 cache: `php -f bin/magento cache:clean`

        ...Apply similiar steps for other data objects that you wish to migrate new incremental data changes. Please note, Reindex step is only needed if you migrate Customers data.  

### - How to disable our tool:
    + Open your Terminal window, go to your Magento 2 folder and you can disable this module by running the command:
     `php -f bin/magento module:disable -c Ubertheme_Ubdatamigration`

### - Let’s discover Magento 2 with your data migrated by URL:
    http://your_magento2_url/