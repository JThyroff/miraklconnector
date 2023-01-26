<h1 id="miraklconnector">MiraklConnector</h1>
<p>Prestashop module to integrate Mirakl orders in Prestashop using the Mirakl sdk.</p>
<p>Repository:
    <a href="https://github.com/JThyroff/miraklconnector/">https://github.com/JThyroff/miraklconnector/</a></p>
<p>Prestashop module documentation:
    <a href="https://devdocs.prestashop-project.org/8/">https://devdocs.prestashop-project.org/8/</a></p>
<p>Prestashop module programming:
    <a href="https://github.com/JThyroff/PrestashopDocs">https://github.com/JThyroff/PrestashopDocs</a></p>
<h2 id="getting-started">Getting started</h2>
<ol>
    <li><p>Install Mirakl sdk as described in Mirakl Faq. (Copy the sdk zip archive to &quot;/miraklconnector/var/zip/&quot;.)</p>
    </li>
    <li><p>Generate the Mirakl Api Credentials. Copy <a href="template.apikey.json">template.apikey.json</a>, rename it to apikey.json and add your credentials.</p>
    </li>
    <li><p>Copy <a href="template.invoicefooter.json">template.invoicefooter.json</a>, rename it to invoicefooter.json and fill in your shop address. </p>
    </li>
    <li><p>Adjust database credentials in <a href="config/services.yml">service.yml</a> and in <a href="src/Mirakl/MiraklDatabase.php">MiraklDatabase.php</a>.</p>
    </li>
    <li><p>Run Composer install to resolve dependencies. </p>
    </li>
    <li><p>Install module in Prestashop Back Office.  </p>
    </li>
</ol>
