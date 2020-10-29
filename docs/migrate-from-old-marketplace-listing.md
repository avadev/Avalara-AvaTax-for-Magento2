### We’ve moved the code for this extension to a different Marketplace listing. In order to download newer versions of the extension in the future, please follow the instructions below.

- The steps below will need to be performed by the developer maintaining your site. The steps should be performed on a development server (not production).
- Log in to your Magento server as, or switch to, the Magento file system owner.
- Change to the directory in which you installed the Magento software.
- To prevent access to your store while it’s being upgraded, switch your store to maintenance mode
  ```
  php <magento_root>/bin/magento maintenance:enable
  ```
- Enter the following command in <magento_root> directory (will remove old package):
  ```
  composer remove classyllama/module-avatax
  ```
- Enter the following command in <magento_root> directory (will install new package):
  ```
  composer require avalara/avatax-magento
  ```
- After applying an update, you must clean the cache.
  ```
  bin/magento cache:clean
  ```
- Update the database schema and data:
  ```
  bin/magento setup:upgrade
  ```
- Generate DI configuration
  ```
  bin/magento setup:di:compile
  ```
- Disable maintenance mode:
  ```
  bin/magento maintenance:disable
  ```
- Thoroughly test the development site to ensure the AvaTax extension is working as expected
- If you had custom developments which have dependencies on classyllama/module-avatax - make sure that you adjust them to the new one avalara/avatax-magento
- Add the changes to source control (these instructions assume you're using Git)
  ```
  git add composer.*
  git commit -m "Change AvaTax extension to use new repository source)
  git push
  ```
