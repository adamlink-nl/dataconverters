# Creating LOD-dataset of the "Beeldbank" (image collection) of the Amsterdam City Archives

These scripts (php) create the datasets <https://data.adamlink.nl/saa/saa-beeldbank/> and <https://data.adamlink.nl/saa-monuments/> containing objects from the image bank ("beeldbanl") of the Amsterdam City Archives ("SAA").

## Introduction
The data was made available to us through an export in the form of XML-files. The compete data set is not available in the public domain but an example of how the XML export looked is provided.

## Scripts

The file `saa-convert.php` triggers the converion from XML to turtle. It reads all XML-files in the `/php/data/saa/beeldbank` directory and identiefies cultural heritage objects.
Each cho is converted to triples, and a few transformations are done:
    
    * dc:contributor strings are converted to AdamLink URI's 
    * portayed persons in dc:subject are also converted to AdamLink URI's 
    * streets in dc:subject are converted to AdamLink URI's 
    * buildings in dc:subject are converted to AdamLink URI's
    * dc:typye is converted to AAT URI's
     
For all objects that do not have copyrighted images, the acutual image is collected through an OpenSearch API and linked to the object.

## How to run the conversion scripts

* git clone this repo and cd into the `php` directory
* run `composer install`
* get the XMl dump files from the city archives from somewhere and put them in the `/php/data/saa/beeldbank` directory
* run `php saa/saa-convert.php` to convert the cho's to triples
* change the run mode in the file to 'image' (on line 25) to gather the link to the images
* the newly created turle files can then be found in the same  `/php/data/saa/beeldbank` directory

