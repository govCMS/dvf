# DVF (Data Visualisation Framework)

The govCMS-CKAN module for Drupal 7, built by Doghouse for govCMS, brought a new
dimension to dynamic data-driven content for Drupal websites.

This is the Drupal 8 evolution of the govCMS_CKAN module, now called the Data
Visualisation Framework. This suite of modules deliver greater customisability
and control, increasing the number of the data types consumed by the module,
and providing more accessibility for developers.

Now included are the popular, industry-standard, JavaScript libraries: C3.js,
based on D3 (for charting), and DataTables (for out-of-the-box table styling).
We have also updated the module to allow for more customisable visualisation
placement using fields.

The modular design of the data import and visualisation formats grants better
customisation options – now supporting CKAN portals and web hosted CSV data
sources with more on the way. It also enables the display of data into line,
bar, spline, area and scatter plots, as well as native table display, again
with more formats possible.

## Related modules

* [CKAN Data Visualisation (dvf_ckan)](https://www.drupal.org/project/dvf_ckan)
  The module that provides data from a CKAN instance (e.g. www.data.gov.au)
* [CKAN Connector (ckan_connect)](https://www.drupal.org/project/ckan_connect)
  Utilised by dvf_ckan to connect to CKAN
* [CSV Data Visualisation (dvf_csv)](https://www.drupal.org/project/dvf_csv)
  The module that provides data from a csv file available on the web

## How it works

DVF is a common "middle man" to build a visualisation from a data source. Eg:
`Data source > DVF > Visualisation` or real world example: `CKAN data > DVF >
Line graph`. Content editors add a data source, then choose how that data to
be displayed.

*DVF provides a field* (storage, widget & formatter) which can be added to any
entity. Sub modules can provide data sources or visualisation plugins that use
this field type, see related modules above for modules currently available.

## <span id="installation_and_configuation">Installing & Configuration</span>

* Install DVF, go to `Extend` and enable a suitable data source (eg. `dvf_csv`).
* Then visit `Manage fields` for the entity type you want to add the
  visualisation to eg `/admin/structure/types/manage/page/fields`
* Click `Add field` and select either `Visualisation URL` or
  `Visualisation File` depending on if the data source will be local or remote,
  provide a name for the field and save.
* Select the `Source type` for the field based on the type of data (requires a
  data source module like `dvf_ckan` or `dvf_csv`).
* Save settings.

## Usage

* Create/edit an entity that contains the field you created above.
* If using `Visualisation URL`, provide a URL to the remote data source
* If using `Visualisation File`, upload a file containing the data (eg. CSV)
* Open `Settings` to configure the visualisation style and the options
  available for that style. Eg. Graph type, axis settings, etc.

## For developers: available hooks

```php
hook_dvf_source_configuration_alter();
hook_dvf_visualisation_data_alter();
hook_dvf_visualisation_build_alter();
hook_dvf_style_configuration_alter();
theme_dvf_style_configuration_alter();

Example code:

/**
 * Implements theme_dvf_style_configuration_alter().
 *
 * Set a custom colour palette for all charts in a custom theme.
 */
function mytheme_dvf_style_configuration_alter(array &$configuration, VisualisationInterface $visualisation) {
  $configuration['chart']['palette'] = '#000000,#aec7e8,#ff0000,#ffbb78,#fff000';
  return $configuration;
}
```

## Example of generating a Bar Graph from a CSV file
1. <a href="#installation_and_configuation">Enable Data Visualisation Module and Configure your Visualisation Field</a>
2. Go to `Content` -> `Add content`, select the content type which you added visualisation field.
3. Scroll down to the visualisation field section. Import your csv file through the file upload field, or you can use
   <a href="#sample_csv_data">Sample CSV Data</a> to have a test.
4. Open up SETTINGS, select `Bar chart`.
   - In DATA SETTINGS, select all the fields by pressing <strong>Shift</strong>.
   - In AXIS SETTINGS, type "Fruits" and "Number of People" in <strong>Axis title</strong> field in `X AXIS SETTINGS`
     and `Y AXIS SETTINGS` respectively.
5. Fill all the other required fields and click `Save`.
6. Open up the content page in the browser, your csv data will be shown in Bar Graph.

#### <span id="sample_csv_data">Sample CSV Data</span>
Please copy and paste the following data into a file and saved as "fruits.csv"<br/>
```
Apple,Orange,Banana,Peach,Blueberry,Grapes
35,30,10,25,40,5
```

## Example of generating a Bar Graph from a JSON file
1. <a href="#installation_and_configuation">Enable Data Visualisation Module and Configure your Visualisation Field</a>
2. Go to `Content` -> `Add content`, select the content type which you added visualisation field.
3. Scroll down to the visualisation field section. Import your JSON file through the file upload field, or you can use
   <a href="#sample_json_data">Sample JSON Data</a> to have a test.
4. Open up SETTINGS, select `Bar chart`.
   - In DATA SETTINGS, select all the fields by pressing <strong>Shift</strong>.
   - In AXIS SETTINGS, type "Fruits" and "Number of People" in <strong>Axis title</strong> field in `X AXIS SETTINGS` and `Y AXIS SETTINGS` respectively.
5. Fill all the other required fields and click `Save`.
6. Open up the content page in the browser, your JSON data will be shown in Bar Graph.

#### <span id="sample_json_data">Sample JSON Data</span>
Please copy and paste the following data into a file and saved as "fruits.json"<br/>
```
[{"Apple": 35,"Orange": 30,"Banana": 10,"Peach": 25,"Blueberry": 40,"Grapes": 5 }]
```

## Example of splitting charts by a column from a CSV file
1. <a href="#installation_and_configuation">Enable Data Visualisation Module and Configure your Visualisation Field</a>

## Development

Development of govCMS8 is currently occurring over at
[GitHub](https://github.com/govCMS/dvf)

Issues can be logged at https://github.com/govCMS/dvf/issues

## Contributing and extending DVF

The flexible design of this module allows for easily creating your own data
sources or visualisations via Drupal 8 Plugins. Examples of visualisations
[here](https://github.com/govCMS/dvf/tree/8.x-1.x/src/Plugin) and data sources
[here](https://github.com/govCMS/dvf/tree/8.x-1.x/dvf_csv).

Submit pull requests [here](https://github.com/govCMS/dvf/pulls).

## Supporting organizations

### Primary Developers

[Doghouse Agency](http://doghouse.agency)

### Sponsors

* [govCMS](https://www.govcms.gov.au/)
* [Department of the Environment and Energy](http://www.environment.gov.au/)
* [Essential Services Commission (Victoria)](https://www.esc.vic.gov.au/)
