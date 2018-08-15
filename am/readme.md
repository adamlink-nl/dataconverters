# /am/

These scripts (python3) create the datasets <https://data.adamlink.nl/am/amcollect/> and <https://data.adamlink.nl/am/amperson/> containing the objects from the museum catalogue of the Amsterdam Museum ("AM").

## introduction
The data from the museumcatalogue is available through an API delivering Adlib native XML. You can manipulate the output of the API by server-side XSLT-transformation.

## testXSLT_amcollect.py
This script sents a request to the API to deliver all records, transforms the data (client-side) into RDF/XML with the adlibXML2rdf.XSLT stylesheet.

## testXSLT_amperson.py
This script sents a request to the API to deliver all records, transforms the data (client-side) into RDF/XML with the adlibPersonXML2rdf.XSLT stylesheet.

The search-response from the API is paged in sets of 100 results. The results are written in the same directory as these scripts in a (large) series of separated Turtle-files per search-result-page.

## make_adamlinks_schema.py
This script adds the AdamLink-specific void:inDataset-triple for <https://data.adamlink.nl/am/amcollect/>.

## see /make_adamlink_uris/ for the final step!
To combine the data into one aggregated dataset we need to replace URIs with our own with owl:sameAs relations.
