# Magento 2 module for update products stocks (quantities)

## Description

This module is only a demo. The main purpose of this module is to present my skills. 

This module downloads a file from an FTP server and them try to update the products stocks.
We can achieve this by executing manually through CLI commands or by cron task.
The file with stock data is in CSV format.

Configuration for this module is placed in `stores->configuration->catalog->stock import`.
There we can find two sections (cron settings and sftp configuration).
Configuration is accessible only to admin users with correct permissions (role resources is `stores->configuration->catalog->stock import`).

### CLI

#### stocksimport:download

`stocksimport:download` will download a file from SFTP server.
This command takes data to connect and credentials from configuration.
Each value of configuration can be overwritten.
Example: `bin/magento stocksimport:download --host test.rebex.net:22 --username demo --password password --timeout 10 --path pub/example/readme.txt --save_as stocks_test.csv`


#### stocksimport:run

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

Other file formats like xml or json can be easily implemented. 
Such an approach is possible by dependency injection.
All you need to do is implement a new class of the reader and inject information about it into config class.
The new reader class need to implement interface `DataFileReaderInterface`.

#### Third party multi stock inventory module

Assuming an optimistic version, we will only need to make minor changes to the code.
In the place where we set the quantity we will probably also have to set the warehouse_id.

#### Unit tests

Not committed yes. Sorry



