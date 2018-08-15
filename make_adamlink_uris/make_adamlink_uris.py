#! /usr/bin/env python3

# replace string with uri's

import rdflib
import os

# set namespaces
owl  = rdflib.Namespace("http://www.w3.org/2002/07/owl#")
rdf  = rdflib.Namespace("http://www.w3.org/1999/02/22-rdf-syntax-ns#")
rdfs  = rdflib.Namespace("http://www.w3.org/2000/01/rdf-schema#")
skos  = rdflib.Namespace("http://www.w3.org/2004/02/skos/core#")

# read AdamLinkGraphs into dict
g = rdflib.Graph()

# print("Reading ...")
# result = g.parse("../adamlinkpersonen.ttl", format="turtle")
# print("AdamLink Person URI's are read")
# print("Reading ...")
# result = g.parse("../adamlinkstraten.ttl", format="turtle")
# print("AdamLink Street URI's are read")
# print("Reading ...")
# result = g.parse("../adamlinkgebouwen.ttl", format="turtle")
# print("AdamLink Building URI's are read")
print("Reading ...")
result = g.parse("../adamlinktypes.ttl", format="turtle")
print("AAT URI's are read")

adamLinkUris = {}
for s,_,o in g.triples((None, rdfs.label, None)):
    adamLinkUris[o] = s

for s,_,o in g.triples((None, skos.prefLabel, None)):
    adamLinkUris[o] = s

for s,_,o in g.triples((None, skos.altLabel, None)):
    adamLinkUris[o] = s

adamLinkUriSet = set(adamLinkUris.keys())

# process original ttl-files
ttlFiles = [x for x in os.listdir() if x.endswith(".tmp.ttl")]
for infile in ttlFiles:
    print(infile) # print progress

    # read file into graph-object
    g = rdflib.Graph()
    result = g.parse(infile, format="turtle")

    # do AdamLink changes
    for s,p,o in g.triples((None, None, None)):

        # replace uri's
        if o in adamLinkUriSet:
            g.remove((s,p,o))
            g.add((s,p,adamLinkUris[o]))

    # write new turtle-file
    outfile = infile
    outfile = outfile.replace(".tmp.",".adm.")
    s = g.serialize(format='turtle')
    f = open(outfile,"wb")
    f.write(s)
    f.close()
