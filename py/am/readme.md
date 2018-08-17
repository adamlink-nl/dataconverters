# Creating LOD-dataset of the Amsterdam Museum collection metadata

These scripts (python3) create the datasets <https://data.adamlink.nl/am/amcollect/> and <https://data.adamlink.nl/am/amperson/> containing the objects from the museum catalogue of the Amsterdam Museum ("AM").

## Introduction
The data from the museumcatalogue is available through an API delivering Adlib native XML. You can manipulate the output of the API by server-side XSLT-transformation. There is information about the collection (amcollect) and the perons (amperson). Data is written in subdirectories org and org_converted. Example-files are in these directories.

## amcollect

### testXSLT_amcollect.py
This script sends a request to the API to deliver all records, transforms the data (client-side) into RDF/XML with the adlibXML2rdf.XSLT stylesheet (which can be used server-side as well).

The search-response from the API is paged in sets of 100 results. The results are written in the directory org/ in separate a Turtle-file per search-result-page.

### make_adamlinks_schema_amcollect.py
This script adds the AdamLink-specific void:inDataset-triple for <https://data.adamlink.nl/am/amcollect/>. Files are read from direcotry org and written to org_converted.

## amperson

### testXSLT_amperson.py
This script sends a request to the API to deliver all records, transforms the data (client-side) into RDF/XML with the adlibPersonXML2rdf.XSLT stylesheet (which can be used server-side as well).

The search-response from the API is paged in sets of 100 results. The results are written in the directory org/ in separate a Turtle-file per search-result-page.

### make_adamlinks_schema_amperson.py
This script adds the AdamLink-specific void:inDataset-triple for <https://data.adamlink.nl/am/amperson/>. Files are read from directory org and written to org_converted.

## see [make_adamlink_uris](https://github.com/adamlink-nl/dataconverters/tree/master/make_adamlink_uris) for the final step!
To combine the data into one aggregated dataset we need to replace locally used URIs with our own AdamLink URIs.
