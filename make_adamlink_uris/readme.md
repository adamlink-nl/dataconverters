# make_adamlink_uris

This script (python3) replaces all original URIs, used as an object in a triple, with an AdamLink URI with a owl:sameAs relation with the original URI.

## introduction
To create a convenient aggregated dataset <https://data.adamlink.nl/adamnet/all/> we mapped the original RDF to one standardized AdamLink specific datamodel. As a final step we convert all the various URIs (eg. for persons VIAF, RKDartists, AM-specific URI's) to our own specific AdamLink URI.

## make_adamlink_uris.py
This script:
- reads the Turtle files with all the owl:sameAs relations between the original URIs and the AdamLink URIs.
- reads an original metadata turtle-file and replaces the URIs
