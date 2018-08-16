#! /usr/bin/env python3

# replace uri's with adamlink-uri's based on owl:sameAs

import rdflib
import urllib.request
import os

# set namespaces
owl  = rdflib.Namespace("http://www.w3.org/2002/07/owl#")
rdf  = rdflib.Namespace("http://www.w3.org/1999/02/22-rdf-syntax-ns#")

# read AdamLinkGraphs into dict
owl = rdflib.Namespace("http://www.w3.org/2002/07/owl#")
g = rdflib.Graph()

# download the latest versions
print("Download latest versions AdamLink URIs ... persons")
#urllib.request.urlretrieve("https://adamlink.nl/data/rdf/persons", "adamlink_uris/adamlinkpersonen.ttl")
print("Download latest versions AdamLink URIs ... streets")
#urllib.request.urlretrieve("https://adamlink.nl/data/rdf/streets", "adamlink_uris/adamlinkstraten.ttl")
print("Download latest versions AdamLink URIs ... buildings")
#urllib.request.urlretrieve("https://adamlink.nl/data/rdf/buildings", "adamlink_uris/adamlinkgebouwen.ttl")
print("Download latest versions AdamLink URIs ... districts")
#urllib.request.urlretrieve("https://adamlink.nl/data/rdf/districts", "adamlink_uris/adamlinkwijken.ttl")

# read AdamLink URIs
print("Reading AdamLink Person URIs ...")
result = g.parse("adamlink_uris/adamlinkpersonen.ttl", format="turtle")
print("Reading AdamLink Street URIs ...")
result = g.parse("adamlink_uris/adamlinkstraten.ttl", format="turtle")
print("Reading AdamLink Building URIs ...")
result = g.parse("adamlink_uris/adamlinkgebouwen.ttl", format="turtle")
print("Reading AdamLink District URI's ...")
result = g.parse("adamlink_uris/adamlinkwijken.ttl", format="turtle")
print("Reading AAT URI's ...")
result = g.parse("adamlink_uris/adamlinktypes.ttl", format="turtle")

adamLinkUris = {}
for s,_,o in g.triples((None, owl.sameAs, None)):
    adamLinkUris[o] = s

adamLinkUriSet = set(adamLinkUris.keys())

# process original ttl-files
ttlFiles = [x for x in os.listdir("in/") if x.endswith(".ttl")]
print("Replacing ...")
for infile in ttlFiles:
    infile = "in/" + infile
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
    outfile = outfile.replace("in/","out/")
    s = g.serialize(format='turtle')
    f = open(outfile,"wb")
    f.write(s)
    f.close()
