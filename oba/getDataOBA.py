#! /usr/bin/env python3

import urllib
import rdflib
from bs4 import BeautifulSoup
import csv

import config # to set access-key to the API in variable "key"

key = config.key
field = "title"
# field = "subject"

oba = rdflib.Graph()
nbt  = rdflib.Graph()

dc   = rdflib.Namespace("http://purl.org/dc/elements/1.1/")
edm  = rdflib.Namespace("http://www.europeana.eu/schemas/edm/")
foaf = rdflib.Namespace("http://xmlns.com/foaf/0.1/")
void = rdflib.Namespace("http://rdfs.org/ns/void#")

oba.bind("dc", dc)
oba.bind("edm", edm)
oba.bind("foaf", foaf)
oba.bind("void", void)

# initialize number of pages
baseUrl = "http://obaliquid.staging.aquabrowser.nl/api/v0/search/" + \
    "?q=" + field + "%3D%22Amsterdam%22" + \
    "&authorization=" + key

print(baseUrl) # debugging & progress

reply = urllib.request.urlopen(baseUrl)
soup = BeautifulSoup(reply, "lxml")
count = soup.find("count")
numberFound = int(count.text)

pages = -(-numberFound // 20) # equivalent to ceiling-function
# pages = 42 # overwrite for debugging purposes
print(pages)

for p in range(1, pages+1):
    requestUrl = baseUrl + "&page=" + str(p)
    print(requestUrl) # debugging & progress
    reply = urllib.request.urlopen(requestUrl)
    soup = BeautifulSoup(reply, "lxml")
    results = soup.find_all("result")

    for result in results:

        # get data from response
        ppns       = result.find_all("ppn-id")
        identifier = result.find("id")
        subjects   = result.find_all("topical-subject")
        summaries  = result.find_all("summary")

        # check for publications in catalogue only
        if identifier.text.startswith("|oba-catalogus|"):

            for ppn in ppns: # iterate through available ppn's
                url  = "https://zoeken.oba.nl/detail/?itemid=" + \
                    urllib.parse.quote(identifier.text)
                uri = "http://data.bibliotheken.nl/id/nbt/p" + ppn.text

                pic = "https://nbc.acc1.bibliotheek.nl/thumbnail?uri=http%3A%2F%2Fdata.bibliotheek.nl%2Fggc%2Fppn%2F" + \
                    ppn.text + "&width=1024&token=fe58476d"

#                pic = "https://cover.biblion.nl/coverlist.dll" + \
#                    "?bibliotheek=oba&ppn=" + ppn.text

                q   = "http://data.bibliotheken.nl/sparql?default-graph-uri=&query=DESCRIBE+%3C" + uri + \
                      "%3E&format=application%2Frdf%2Bxml&timeout=0&debug=on"

#                print(uri) # debugging & progress
#                print(q) # debugging & progress

                # check if PPN uri exists
                b = rdflib.Graph()
                r = b.parse(q)

                if len(b) > 0: # mostly: no errors, but no graph either ...
                    nbt = nbt + b
                    pub = rdflib.URIRef(uri)
                    url  = rdflib.URIRef(url)
                    pic  = rdflib.URIRef(pic)

                    oba.add( (pub, dc.identifier, rdflib.Literal(identifier.text)) )
                    oba.add( (pub, edm.isShownAt, url) )
                    oba.add( (pub, foaf.depiction, pic) )

                    for subject in subjects:
                        oba.add( (pub, dc.subject, rdflib.Literal(subject.text)) )

                    for summary in summaries:
                        oba.add( (pub, dc.description, rdflib.Literal(summary.text)) )

    # write every x pages into a file
    x = 1
    if (p % x) == 0:
        # serialize and write to file
        nr = int(p/x)

        s = oba.serialize(format='turtle')
        filename = "org/OBAcat_" + field + str(nr) + ".ttl"
        file = open(filename,"wb")
        file.write(s)
        file.close()
        oba = rdflib.Graph()
        oba.bind("dc", dc)
        oba.bind("edm", edm)
        oba.bind("foaf", foaf)
        oba.bind("void", void)

        s = nbt.serialize(format='turtle')
        filename = "org/NBTinOBA_" + field + str(nr) + ".ttl"
        file = open(filename,"wb")
        file.write(s)
        file.close()
        nbt = rdflib.Graph()

if len(oba) > 0 and len(nbt) > 0:
    s = oba.serialize(format='turtle')
    filename = "org/OBAcat_" + field + "rest.ttl"
    file = open(filename,"wb")
    file.write(s)
    file.close()

    s = nbt.serialize(format='turtle')
    filename = "org/NBTinOBA_" + field + "rest.ttl"
    file = open(filename,"wb")
    file.write(s)
    file.close()
