# Magento 2 module for update products stocks (quantities)

## Description

This module is only a demo. The main purpose of this module is to present my skills. From the recruiter I got this task:

```
Task is based on Magento 2

We need to have functionality that will import stock data from sftp.
Configurations must be placed in admin backend and be accessible only to admin users with correct permissions.

Stock data might be in csv, xml, json format because we import them from different manufacturers. Please choose one of the above formats to implement, just having in mind that multiple formats should be easily supported.

CSV columns are always like there is no header with columns names:
Stock id (manufacturer internal)
Product sku
Quantity
Warehouse id (where product is placed)

XML format is:

<?xml version="1.0" encoding="UTF-8"?>
    <item>
        <id>product sku</g:id>
        <gtin>5060472351036</gtin>
        <condition>New</g:condition>
        <color>Black</g:color>
        <mpn>5060472351036</mpn>
        <brand>Some Brand</brand>
        <availability>in stock</availability>
        <price>599 GBP</price>
        <quantity>5.0000</quantity>
    </item>
	
JSON always has the same structure as xml file format.

Currently we are not using any third party multistock inventory module, but we might want to use it in future. Optional - you can describe multiple inventory approach instead of implementing it.

We need multiple entry points to application. Cron,Console and admin ui based. Please choose one of the entry points, while having in mind multiple entry points support.

Cron settings (time when is run) must be editable in admin backend panel next to credentials configuration.
```

I choose the CSV format to implement. 
Configuration is placed in `stores->configuration->catalog->stock import`.
There we can find two sections (cron settings and sftp configuration).
Configuration is accessible only to admin users with correct permissions (role resources is `stores->configuration->catalog->stock import`).
I made two entry points (cron and console).

### CLI

`stocksimport:download` will download a file from SFTP server.
This command takes data to connect and credentials from configuration.
Each value of configuration can be overwritten.
Example: `bin/magento stocksimport:download --host test.rebex.net:22 --username demo --password password --timeout 10 --path pub/example/readme.txt --save_as stocks_test.csv`

`stocksimport:run` will import stocks data from file. 
We can specify a custom file which is placed in `path/to/project/var/tmp/`.
We can also setup a custom batch size for optimize performance.
Example: `bin/magento stocksimport:run --file stocks_test.csv --batch 10`

### Cron job

Cron job can be disabled/enabled from configuration.
By default this job is disabled (to prevent accidental resetting of inventory).
Any logs from execution of this process can be found in `path/to/project/var/log/system.log`.
This job will download a CSV file from SFTP server and will try to import data.

### CSV file

Example of CSV file:

```
"AAA1","24-MB01",10,"AAA"
"AAA2","24-MB02",11,"AAA"
"AAA3","24-WB01",31,"AAA"
"AAA3","24-WB01",31,"BBB"
```

### General discussion

#### Other file formats

`having in mind that multiple formats should be easily supported`
Such an approach is possible by dependency injection.
All you need to do is implement a new class of the reader and inject it into config class.

#### Third party multi stock inventory module

Assuming an optimistic version, we will only need to make minor changes to the code.
In the place where we set the quantity we will probably also have to set the warehouse_id.

#### Unit tests

Not committed yes. Sorry



