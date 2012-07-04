# HXL Vocabulary 

Version history and development tools for the **Humanitarian eXchange Language (HXL)** vocabulary (see [hxl.humanitarianresponse.info](http://hxl.humanitarianresponse.info)).

## Requirements

- Server with PHP on localhost
- [Raptor RDF Syntax Library](http://librdf.org/raptor/) (LGPL / Apache 2.0 license)
- [EasyRDF](http://www.aelius.com/njh/easyrdf/) (BSD license)
- [Graphviz](http://www.graphviz.org/) (Eclipse Public License)

## How-to

1. Create a folder named *hxl* in the htdocs folder of your webserver. Move the contents of the tools folder into the hxl folder.
2. To change the vocabulary, edit the *hxl.ttl* file in the tools folder. 
3. Once you are done, execute *run.sh* (you may need to edit this file a bit if you are on a Windows system)
4. That's it.