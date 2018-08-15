# /oba/

These scripts (python3) create the dataset <https://data.adamlink.nl/oba/adamcat/> containing a selection of publications about Amsterdam from the library catalogue of the Amsterdam Public Library ("Openbare Bibliotheek Amsterdam, or "OBA").

## introduction
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

The search-response from the API is paged in sets of 100 results. The results are written in the same directory as these scripts in a (large) series of separated Turtle-files per search-result-page.

## make_adamlinks_schema.py
This script maps the datamodel of data.bibliotheken.nl to our datamodel (mainly from schema.org to Dublin Core).

## see /make_adamlink_uris/ for the final step!
To combine the data into one aggregated dataset we need to replace URIs with our own with owl:sameAs relations.
