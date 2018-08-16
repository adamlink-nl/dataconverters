# Creating LOD-dataset of the Amsterdam Public Library collection metadata

These scripts (python3) create the dataset <https://data.adamlink.nl/oba/amcat/> containing a selection of publications about Amsterdam from the library catalogue of the Amsterdam Public Library ("Openbare Bibliotheek Amsterdam, or "OBA").

## Introduction
The data of the catalogue of the OBA is available through an API, delivering XML.

## config.py
You need an API-key. Rename the `config.py.dist` to `config.py` and put the key in this file. Contact the OBA for more information.

## getDataOBA.py
This script searches for publications with the string "Amsterdam" in a particular field of the metadata.
We used it to find the string "Amsterdam" in subject-field and the title-field respectively.
Then:
- it finds the equivalent URI in https://data.bibliotheken.nl/. If none is found there, the publication is skipped.
- it writes some OBA-local data we obtained from the API
- creates a link to an images of the cover of the book

The search-response from the API is paged in sets of 20 results. The results are written in the directory org/ in separate a Turtle-file per search-result-page. NB sometimes there are less than 20 publications in a file, if they are skipped because the script did not resolve a URI on data.bibliotheken.nl.

## make_adamlinks_schema.py
This script maps the datamodel of data.bibliotheken.nl to our datamodel (mainly from schema.org to Dublin Core). Files are read from directory org and written to org_converted.

## see [make_adamlink_uris](https://github.com/adamlink-nl/dataconverters/tree/master/make_adamlink_uris) for the final step!
To combine the data into one aggregated dataset we need to replace locally used URIs with our own AdamLink URIs.
