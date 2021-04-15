### Website owner does not able to upgrade to the newer version of extension due to MySQL DDL errors.

The issue is often happening when customers start using tables with prefixes.

Extension prior 2.2.3 versions do not support tables with prefixes. 

So when customers start using tables with prefixes for Magento it could happen that extension’s install scripts do not work as expected, trying to create tables that already exist or rename columns that already were renamed.

Commonly SQL error that appears during execution of setup:upgrade command could be the following:

installing schema... Upgrading schema... Column "cross_border_type" does not exist in table "faf3_avatax_cross_border_class".

we could suggest the following steps of resolving this issue:
1. please, try with your local environment first
2. please, download [db-uninstall.sql](./db-uninstall.sql) script
3. replace {Prefix} variable with an appropriate prefix that you have used.
4. execute modified db-uninstall.sql script on your database. This script drops all extension’ table’ and removes the record from setup_module table. This will allow us to create DB schema from scretch.
5. composer remove avalara/avatax-magento
6. composer require avalara/avatax-magento:2.2.3  //2.2.3 ver. or higher
7. php ./bin/magento setup:upgrade
8. php ./bin/magento setup:di:compile

please, take into consideration that after you execute the db-uninstall script website will lose data related to the extension, such as logs, queues, cross border configuration and data related to old invoices, orders and credit memos. Extension configuration and products configuration will be present after reinstallations. 
