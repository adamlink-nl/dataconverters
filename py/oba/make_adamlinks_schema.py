#! /usr/bin/env python3

import rdflib
import os

# set namespaces
dc  = rdflib.Namespace("http://purl.org/dc/elements/1.1/")
rdf  = rdflib.Namespace("http://www.w3.org/1999/02/22-rdf-syntax-ns#")
edm  = rdflib.Namespace("http://www.europeana.eu/schemas/edm/")
void = rdflib.Namespace("http://rdfs.org/ns/void#")
schema = rdflib.Namespace("http://schema.org/")

# set uri's
dataset = rdflib.URIRef("https://data.adamlink.nl/oba/amcat/")
booktype = rdflib.URIRef("http://vocab.getty.edu/aat/300028051")
pertype = rdflib.URIRef("http://vocab.getty.edu/aat/300026657")

# process original ttl-files
ttlFiles = [x for x in os.listdir("org/") if x.endswith(".ttl")]
for infile in ttlFiles:
    infile = "org/" + infile
    print(infile) # print progress

    # read file into graph-object
    g = rdflib.Graph()
    g.namespace_manager.bind('void', void, override=False)
    result = g.parse(infile, format="turtle")

    # do AdamLink changes
    for s,p,o in g.triples((None, None, None)):

        # add void:inDataset
        if p == dc.identifier:
            g.add((s,void.inDataset, dataset))

        # add dc.type
        if p == rdf.type and o == schema.Book:
            g.add((s,dc.type, booktype))
        if p == rdf.type and o == schema.Periodical:
            g.add((s,dc.type, pertype))

        # replace mapping schema to dc
        if p == schema.about:
            g.add((s,dc.subject, o))
            g.remove((s,p,o))
        if p == schema.author:
            g.add((s,dc.creator, o))
            g.remove((s,p,o))
        if p == schema.contributor:
            g.add((s,dc.contributor, o))
            g.remove((s,p,o))
        if p == schema.name:
            g.add((s,dc.title, o))
            g.remove((s,p,o))

    # write new turtle-file
    outfile = infile
    outfile = outfile.replace("org/","org_converted/")
    s = g.serialize(format='turtle')
    f = open(outfile,"wb")
    f.write(s)
    f.close()
