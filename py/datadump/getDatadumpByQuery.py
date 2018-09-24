#! /usr/bin/env python3

from SPARQLWrapper import SPARQLWrapper, N3
from rdflib import Graph

endpoint = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql"

with open ("beeldmateriaal.rq", "r") as myfile:
    q = myfile.read()

sparql = SPARQLWrapper(endpoint)
offset = 0
start = 0
stop = 2

while start < stop:
    query = q + " OFFSET " + str(start * 10000)
    sparql.setQuery(query)

    sparql.setReturnFormat(N3)
    results = sparql.query().convert()
    g = Graph()
    g.parse(data=results, format="n3")
    if len(g) > 0:
        s = g.serialize(format='pretty-xml')

        filename = "beeldmateriaal/dump" + str(start) + ".rdf.xml"
        f = open(filename,"wb")
        f.write(s)
        f.close()
    else:
        stop = 0

    start = start + 1
