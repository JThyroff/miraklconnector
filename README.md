# MiraklConnector

Prestashop module to integrate Mirakl orders in Prestashop using the Mirakl sdk. This module adds an extra tab with a table in the backoffice showing mirakl orders. Providing a functionality to generate pdf invoices for each order.

Repository:
<https://github.com/JThyroff/miraklconnector/>

Prestashop module documentation:
<https://devdocs.prestashop-project.org/8/>

Prestashop module programming:
<https://github.com/JThyroff/PrestashopDocs>

## Getting started

1. Install Mirakl sdk as described in Mirakl Faq. (Copy the sdk zip archive to "/miraklconnector/var/zip/".)

2. Generate the Mirakl Api Credentials. Copy [template.apikey.json](template.apikey.json), rename it to apikey.json and add your credentials.

3. Copy [template.invoicefooter.json](template.invoicefooter.json), rename it to invoicefooter.json and fill in your shop address. 

4. Adjust database credentials in [service.yml](config/services.yml) and in [MiraklDatabase.php](src/Mirakl/MiraklDatabase.php).

5. Run Composer install to resolve dependencies. 

6. Install module in Prestashop Back Office.  
