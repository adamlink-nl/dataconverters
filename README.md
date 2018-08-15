# dataconverters
Converts various sources of cultural heritage data into RDF

The AdamNet Foundation creates Linked Open Data from cultural heritage metadata in Amsterdam (NL). Therefor it:
- creates a list of reference terms that link the metadata for streets, buildings, districts and persons (available on https://www.adamlink.nl)
- converts existing datasets of participating cultural heritage institutes into RDF (subject of this rep)
- makes it available on SPARQL-endpoints via https://data.adamlink.nl/

## Cultural Heritage Datasets

### [am](https://github.com/adamlink-nl/dataconverters/tree/master/py/am) - Amsterdam Museum - museumobjects (paintings, drawsings, etches ...)
The complete collection

### [oba](https://github.com/adamlink-nl/dataconverters/tree/master/py/oba) Public Library - publications (mainly books)
Selection of the collection, with publication about Amsterdam

## Converting into AdamLink

### [make_adamlink_uris](https://github.com/adamlink-nl/dataconverters/tree/master/py/make_adamlink_uris) make_adamlink_uris
Normalization of aggregated dataset for convenient usage

## Extra data

### [ecartico](https://github.com/adamlink-nl/dataconverters/tree/master/py/ecartico) University of Amsterdam (Persons)
Set of persons mainly from the seventeenth and eighteenth century
