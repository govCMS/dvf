# DVF (Data Visualisation Framework)

<img src="https://github.com/govCMS/dvf/wiki/images/dvf-logo.png" width="460"/>

The Data Visualisation Framework (DVF for short) is a Drupal 8/9/10 module that
allows you to turn boring data sources (Eg CSV or JSON file) into interactive
visualisation. This allows content authors to provide more meaning to raw data,
illustrate trends and engage users.

The first version of DVF (named [govcms_ckan](https://www.drupal.org/project/govcms_ckan))
was developed for the Australian [State of the environment report](https://soe.environment.gov.au/)
with the goal of embedding data from [data.gov.au](https://data.gov.au/) as visualisations.
This provided context to the data and allowed the report to clearly demonstrate
evidence.

DVF can be found on [Drupal.org here](https://www.drupal.org/project/dvf)

## What's in the box?

For visualisations, DVF includes popular, industry-standard, JavaScript
libraries such as:

* [billboard.js](https://naver.github.io/billboard.js/) based on D3 (for charting)
* [DataTables](https://datatables.net/) (for out-of-the-box table styling).

View some [Examples](https://github.com/govCMS/dvf/wiki/Examples) of how these might
look.

For data sources, DVF supports CSV, JSON and CKAN data sources however you can
always [extend](https://github.com/govCMS/dvf/wiki/Extending-DVF) DVF and add
additional data sources.

## Related modules

* [CKAN Connector (ckan_connect)](https://www.drupal.org/project/ckan_connect)
  Required by dvf_ckan to connect and retrieve data from CKAN sources

## How it works

DVF is a common "middle man" to build a visualisation from a data source. Eg:
`Data source > DVF > Visualisation` or real world example: `CKAN data > DVF >
Line graph`. Content editors add a data source, then choose how that data to
be displayed.

*DVF provides a field* (storage, widget & formatter) which can be added to any
entity. Sub modules can provide data sources or visualisation plugins that use
this field type, see related modules above for modules currently available.

## Installing & Configuration

Visit the [Quick start guide](https://github.com/govCMS/dvf/wiki/Quick-Start) for
information on how to install and configure DVF

## Usage

Guides are available on different ways you might want to use DVF. You can also
check out our [Examples](https://github.com/govCMS/dvf/wiki/Examples) page to
see what the final product might look like.

* [CSV Datasource](https://github.com/govCMS/dvf/wiki/CSV-Datasource)
* [JSON Datasource](https://github.com/govCMS/dvf/wiki/JSON-Datasource)
* [CKAN Datasource](https://github.com/govCMS/dvf/wiki/CSV-Datasource)

## Extending DVF (for developers)

Documentation on extending DVF (via hooks or plugins) can be found here

* [Extending DVF overview and hooks](https://github.com/govCMS/dvf/wiki/Extending-DVF)
* [Adding a new source](https://github.com/govCMS/dvf/wiki/Adding-New-Source)
* [Adding a new visualisation](https://github.com/govCMS/dvf/wiki/Adding-New-Visualisations)

## DVF core development

Development of DVF is currently occurring over at [GitHub](https://github.com/govCMS/dvf)

Issues are ideally logged in the [Github issue queue](https://github.com/govCMS/dvf/issues)
but we also monitor the [Drupal issue queue](https://www.drupal.org/project/issues/dvf)

## Contributing and extending DVF

We welcome (and appreciate) improvements and fixes to DVF, so if you have
something to add please submit a [Github pull request](https://github.com/govCMS/dvf/pulls).

Ideally [Tests](https://github.com/govCMS/dvf/wiki/Tests) should accompany new
features.

## Supporting organizations

### Primary Developers

[Doghouse Agency](http://doghouse.agency)

### Sponsors

* [govCMS](https://www.govcms.gov.au/)
* [Department of the Environment and Energy](http://www.environment.gov.au/)
* [Essential Services Commission (Victoria)](https://www.esc.vic.gov.au/)
