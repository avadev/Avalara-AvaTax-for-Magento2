<!-- This list is in each of the documentation files. Ensure any updates are applied to the list in each file. -->
##### We’ve moved the code for this extension to a different Marketplace listing. In order to download newer versions of the extension in the future, please follow the instructions below

- Log in to your Magento server as, or switch to, the Magento file system owner.
- Change to the directory in which you installed the Magento software.
- To prevent access to your store while it’s being upgraded, switch your store to maintenance mode
  ```
  php <magento_root>/bin/magento maintenance:enable
  ```
- Enter the following command in <magento_root> directory:
  ```
  composer remove classyllama/module-avatax
  ```
- After applying an update, you must clean the cache.
  ```
  bin/magento cache:clean
  ```
- Clear the var and generated subdirectories:
  ```
  rm -rf <Magento install dir>/var/cache/*
  rm -rf <Magento install dir>/var/page_cache/*
  rm -rf <Magento install dir>/generated/code/*
  ```
- Update the database schema and data:
  ```
  bin/magento setup:upgrade
  ```
- Enter the following command in <magento_root> directory:
  ```
  composer require avalara/avatax-magento
  ```
- After applying an update, you must clean the cache.
  ```
  bin/magento cache:clean
  ```
- Clear the var and generated subdirectories:
  ```
  rm -rf <Magento install dir>/var/cache/*
  rm -rf <Magento install dir>/var/page_cache/*
  rm -rf <Magento install dir>/generated/code/*
  ```
- Update the database schema and data:
  ```
  bin/magento setup:upgrade
  ```
- Disable maintenance mode:
  ```
  bin/magento maintenance:disable
  ```