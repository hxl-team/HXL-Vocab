<?php 
require_once "EasyRdf.php";
require_once "html_tag_helpers.php";


// some utilities
class CUtils{
	
	private $_vocab = null;
	
	public function __construct($vocab=null)
    {
        if ($vocab) {
            $this->_vocab = $vocab;
        }
    }
	
	// if the resource URI has a fragment, return it; otherwise, return the whole URI:
	public function getFragment(EasyRdf_Resource $resource){
		$uri = $resource->__toString();
		$parts = explode("#", $uri);
		if(count($parts) > 1){
			return $parts[1];
		}else{
			return $parts[0];
		}
		
	}

	// return the fragement if the resource is part of this vocab; if not, return the whole URI
	public function getHXLFragment(EasyRdf_Resource $resource){
		if(strpos($resource, $this->_vocab) === 0){
			return $this->getFragment($resource);
		}else{
			return $resource->__toString();
		}
		
		
	}

	
	// generates a html link with the label as text, depending on whether it is internal to the vocab, or external
	public function makeLink(EasyRdf_Resource $res){
			$resource = $res->__toString();
			
			if($this->getFragment($res) == "BaseClass"){
				return 'hxl:BaseClass';
			}else{
				if(strpos($resource, $this->_vocab) === 0){ // internal
					return '<a href="#'.$this->getFragment($res).'">'.$res->label().'</a>';
				}else{ // external
					return '<a href="'.$resource.'">'.$resource.'</a>';
				}
			}
			
	}

	// generates an html link with the resource ID as text, depending on whether it is internal to the vocab, or external
	public function makeIDLink(EasyRdf_Resource $res){
			$resource = $res->__toString();
			
			if(strpos($resource, $this->_vocab) === 0){ // internal
				return '<a href="#'.$this->getFragment($res).'">'.$this->getFragment($res).'</a>';
			}else{ // external
				return '<a href="'.$resource.'">'.$resource.'</a>';
			}
	}
	
	// gets the properties where $class (or any of its superclasses) is rdfs:domain
	public function getDomains($graph, $class){
		$domainURIs = $graph->resourcesMatching('rdfs:domain', $class);

		$domains = array();
		foreach($domainURIs as $domainURI){
			$domains[] = $this->makeLink($domainURI);			
		}

		// document inherited domains from superclasses
		$domains = $this->getInheritedDomains($graph, $class, $domains); 
		
		if(count($domains) > 0){
			$domains = array_unique($domains); // remove duplicates
			sort($domains);
			print'	 <tr><th>Domain of:</th><td>';
			foreach($domains as $domain){
				print $domain.' | ';							
			}			
			print'</td>';
		}
	}
	
	
	// iterates through the transitive closure of the class hierarchy and prints all 
	// properties where a superclass of $res is the domain
	public function getInheritedDomains(EasyRdf_Graph $graph, EasyRdf_Resource $res, Array $domains){
			
			$superclasses = $res->all("rdfs:subClassOf");
			foreach($superclasses as $super){
				$domainURIs = $graph->resourcesMatching('rdfs:domain', $super);
				foreach($domainURIs as $domainURI){
					$domains[] = '<span class="inherited">'.$this->makeLink($domainURI).' <small>(via '.$this->makeLink($super).')</small></span>';
				}
				$domains = array_merge($domains, $this->getInheritedDomains($graph, $super, $domains)); //recursion
			}
		 	
			// end of recursion
			return $domains;		
	}
	
	
	// gets the properties where $class (or any of its subclasses) is rdfs:range
	public function getRanges($graph, $class){
		$rangeURIs = $graph->resourcesMatching('rdfs:range', $class);

		$ranges = array();
		foreach($rangeURIs as $rangeURI){
			$ranges[] = $this->makeLink($rangeURI);			
		}

		// document inherited ranges from subclasses
		$ranges = $this->getInheritedRanges($graph, $class, $ranges); 
		
		if(count($ranges) > 0){
			$ranges = array_unique($ranges); // remove duplicates
			sort($ranges);
			print'	 <tr><th>Range of:</th><td>';
			foreach($ranges as $range){
				print $range.' | ';							
			}			
			print'</td>';
		}
	}
	
	
	
	// iterates through the transitive closure of the class hierarchy and prints all 
	// properties where a subclass of $res is the range
	public function getInheritedRanges(EasyRdf_Graph $graph, EasyRdf_Resource $res, Array $ranges){		
			//$subclasses = $graph->resourcesMatching('rdfs:subClassOf', $res);
			$subclasses = $res->all("rdfs:subClassOf");
			foreach($subclasses as $sub){
				//error_log($sub.' is a subclass of '.$res);
				$rangeURIs = $graph->resourcesMatching('rdfs:range', $sub);
				foreach($rangeURIs as $rangeURI){
					$ranges[] = '<span class="inherited">'.$this->makeLink($rangeURI).' <small>(via '.$this->makeLink($sub).')</small></span>';
				}
				$ranges = array_merge($ranges, $this->getInheritedRanges($graph, $sub, $ranges)); //recursion
			}
			
			// end of recursion
			return $ranges;
	}					
}


$graph = new EasyRdf_Graph( 'http://localhost/HXL-Vocab/Tools/hxl.rdf' );
$graph->load();
if ($graph) {
	// generate information about ontology:
	$vocabs = $graph->allOfType('owl:Ontology');
	$vocab = $vocabs[0];
} else {
	print "<p>Failed to create graph.</p>";
}

$u = new CUtils($vocab->__toString());


echo'<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xml:lang="en"
      xmlns="http://www.w3.org/1999/xhtml"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      xmlns:owl="http://www.w3.org/2002/07/owl#"
      xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
      xmlns:dc="http://purl.org/dc/terms/"
      xmlns:foaf="http://xmlns.com/foaf/0.1/"
      xmlns:wot="http://xmlns.com/wot/0.1/"
      xmlns:prv="http://purl.org/net/provenance/ns#"
      xmlns:opmv="http://purl.org/net/opmv/ns#"
>
<head>
 	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  	<link href="css/hxl.css" rel="stylesheet">
  	<title><?php print $vocab->label(); ?></title>
<!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
    <style type="text/css">
    h3{
    	margin-top: 45px;
    }

    h4{
    	margin-top: 35px;
    }
	</style>

    <link rel="shortcut icon" href="img/favicon.ico">
</head>


<body data-spy="scroll" data-target=".navspy" onload="prettyPrint()">
	<div class="container">
	<div class="navbar">
        <div class="container">
          <div class="nav-hxlator">
          <span class="brand" style="padding-top: 0"><img src="img/logo.png"></span>        
            <ul class="nav" id="topnav">

			<li><a href="http://hxl.humanitarianresponse.info/docs/">HXL Documentation</a></li>
			<li class="active"><a href="http://hxl.humanitarianresponse.info/ns/">HXL Vocabulary</a></li>           
            </ul>
          </div>
      </div>
    </div> 


<div class="container start">	    
    	<div class="row">
	     	<div class="span4" style="text-align: center">
	    		<img src="img/tag.png" />
	    		<div class="navspy">
	    			<ul class="nav nav-tabs nav-stacked affix-top sidenav" data-spy="affix" data-offset-top="314">
	  					<li><a href="#sec-intro">Introduction <i class="icon-chevron-right pull-right"></i></a></li>
  						<li><a href="#sec-glance">HXL at a glance <i class="icon-chevron-right pull-right"></i></a></li>
  						<!-- <li><a href="#sec-specification">Cross-reference for core HXL classes and properties by section: <i class="icon-chevron-right pull-right"></i></a></li> -->
  						<?php
						
						// get all vocabulary sections (i.e., each resource that is 
						// used as object in rdfs:isDefinedBy statements):
						
						$sections = $graph->allOfType($vocab."#vocabularySection");
				 		sort($sections);
						
						foreach($sections as $section){
							print '<li><a href="#'.$u->getHXLFragment($section).'">'.$section->get("dc:title").' <i class="icon-chevron-right pull-right"></i></a></li>';
						}
						
						
						?>					
				  <li><a href="#sec-completegraph">Graph overview of the complete vocabulary <i class="icon-chevron-right pull-right"></i></a></li>
				  <li><a href="#sec-license">License <i class="icon-chevron-right pull-right"></i></a></li>
				  <li><a href="#sec-changes">Change log <i class="icon-chevron-right pull-right"></i></a></li>
</ol>     
			        </ul>		
			    </div>	
	      	</div>
	      	<div class="span8">	      				       	      	

<h1 about="" property="dc:title" xml:lang="en"><?php print $vocab->label(); ?></h1>

<hr />

<dl>
  <dt>Namespace URI for the HXL Vocabulary:</dt>
  <dd><a href="<?php print $vocab; ?>"><?php print $vocab; ?></a></dd>
  <dt>Revision:</dt>
  <dd><?php 
$date = new DateTime($vocab->get('dc:date'));
print $date->format('F j, Y'); ?></dd>  
  <dt>Authors:</dt>
   <?php
   	$authors = $vocab -> all('dc:creator');
	foreach ($authors as $author){
		if($author->hasProperty("foaf:homepage")){
			print '<dd about="' . $author . '" typeof="foaf:Person">';
			print '<a rel="foaf:homepage" href="'. $author->get('foaf:homepage').'" property="foaf:name">';
			print $author->get('foaf:name');
			print '</a>';
		}else{
			print '<dd about="' . $author . '" typeof="foaf:Person" property="foaf:name">';
			print $author->get('foaf:name');
		}
		print '</dd>';
	}
   ?>
   <dt>Further information:</dt>
   <dd><?php print $vocab->get('rdfs:seeAlso'); ?></dd>
   <dt>Formats:</dt>
   <dd>Besides this human-readable form, the HXL vocabulary specification is also available in various machine-readble RDF serializations: <a href="hxl.rdf">RDF/XML</a> | <a href="hxl.ttl">Turtle</a> | <a href="hxl.n3">N-Triples</a>.</dd>
<p style='clear:both'>The XML Namespace URI that should be used by implementations of this version of the specification is: <a href="<?php print $vocab; ?>"><?php print $vocab; ?></a>.</p> 

<p>The preferred prefix for the vocabulary is <strong>hxl</strong>.</p>

</dl>

<!-- <h2 id="sec-toc">Table of contents</h2>
<ol>
  <li><a href="#sec-intro">Introduction</a></li>
  <li><a href="#sec-glance">The HXL vocabulary at a glance</a></li>
  <li><a href="#sec-specification">Cross-reference for core HXL classes and properties by section</a></li>
	<ol>
		<?php
		
		// get all vocabulary sections (i.e., each resource that is 
		// used as object in rdfs:isDefinedBy statements):
		
		$sections = $graph->allOfType($vocab."#vocabularySection");
 		sort($sections);
		
		foreach($sections as $section){
			print '<li><a href="#'.$u->getHXLFragment($section).'">'.$section->get("dc:title").'</a></li>';
		}
		
		
		?>
	</ol>
  <li><a href="#sec-completegraph">Integrated graph overview of the complete HXL vocabulary</a></li>
  <li><a href="#sec-license">License</a></li>
  <li><a href="#sec-changes">Change log</a></li>
</ol> -->

<hr />

<h2 id="sec-intro">Introduction</h2>

<p about="<?php print $vocab; ?>" property="dc:abstract">
	<?php print $vocab->get('dc:abstract'); ?>
</p>

<p>The Humanitarian eXchange Language is defined as a set of classes and properties, using the Resource Description Framework (<a href='http://www.w3.org/RDF/'>RDF</a>) as underlying technology. This enables HXL to publish humanitarian data as part of the <a href='http://linkeddata.org/'>Linked Open Data cloud</a>. We recommend <a href='http://www.ted.com/talks/tim_berners_lee_on_the_next_web.html'>Tim Berners-Lee's TED Talk</a> as a quick introduction to Linked Data and RDF, and <a href='http://linkeddatabook.com'>the Linked Data book</a> by Tom Heath and Chris Bizer as a more in-depth, technical primer to the topic.</p>

<p>The following series of figures illustrates the basics of RDF and Linked Data that are required to understand this document.</p> 

<p class='intro'><img src='intro1.dot.png'  align='right'>RDF is based on statements of the form <em>subject - predicate - object</em>. Subject and object are illustrated as ellipses, the prediacte &ndash; or <em>property</em> &ndash; connects them and points from subject to object.</p>

<p class='intro'><img src='intro2.dot.png' align='right'>URLs act as unique identifiers for subjects, properties and objects, as shown here. This way, more information about these can be found out by visiting the URLs. In case of the property, it should return its definition &ndash; again in RDF. </p>

<p class='intro'><img src='intro3.dot.png'  align='right'>Namespace declarations can be used to make the URLs more compact. In this standard, we only show the namespace prefix if the class or property has been defined in an external standard. Moreover, the object of a statement (or <em>triple</em>) can also be a <em>literal</em>, such as a string or a number. Literals are visualized as boxes.</p>

<p class='intro'><img src='intro4.dot.png'  align='right'>Any subjects and (non-literal) objects should be typed. The classes defined in this standard declare the types that can be used for this purpose.</p>

<p class='intro'><img src='intro5.dot.png'  align='right'>The definitions of these classes are based on subclass hierarchies, indicated by a dotted red line in the figure. <em>Emergency</em> is a subclass of <em>Situation</em>, for example. Subclasses inherit from their superclasses, most importantly in terms of the domain and range of properties.</p>

<p class='intro'><img src='intro6.dot.png'  align='right'>The domain and range define the types of things between which a property can be used. Consider the property <a href='#hasObjective'>hasObjective</a>, whose <em>domain</em> is defined as <a href='#Activity'>Humanitarian Activity</a>. This states that whenever we see a statement using hasObjective as property, we can infere that the <em>subject</em> of this triple is a Humanitarian Activity. Likewise, its <em>range</em> is defined as <a href='#Objective'>Objective</a>, so that we can infere that the <em>object</em> in any statement using hasObjective is an Objective. In the graphs shown in this standard, the property arrows point from domain to range (i.e., from kinds of subjects to kinds of objects). 


<h2 id="sec-glance">The HXL vocabulary at a glance</h2>

<p>An alphabetical index of all terms from the HXL vocabulary, by class and by property, is given below for quick reference. Click the terms for a more detailed description.</p>

<div class="hero-unit" style="padding: 15px">

<p><strong>Classes</strong> |
  

<?php 
	// index of all classes in alphabetical order:
	
	$classes = $graph->allOfType('rdfs:Class'); 
	sort($classes);
	foreach($classes as $class){
		print $u->makeLink($class).' | ' ;
	}
	
?>
  
</p>

</div>

<div class="hero-unit" style="padding: 15px">

<p><strong>Properties</strong> |

<?php 
	// index of all properties in alphabetical order:
	
	$properties = $graph->allOfType('rdf:Property'); 
	sort($properties);
	foreach($properties as $property){
		print $u->makeLink($property).' | ' ;
	}
?>
</p>

</div>

  

<div class="overview">
<h2 id="sec-specification">Cross-reference for all HXL classes and properties</h2>

<p>The cross-reference is organized into thematic sections. Each section is illustrated by a graph of the respective classes and properties. The ellipses in the graph represent classes, whereas the arrows represent properties. Dashed lines indicate a subclass relationship (e.g. <a href="#Emergency">Emergency</a> is a  subclass of <a href="#Situation">Situation</a>). Solid lines are labeled with the property that connects these two classes. Grey ellipses indicate <em>adjacent</em> classes that are defined in a different section.</p> 

<p>Jump directly to the different sections: | 
<?php

foreach($sections as $section){
	print '<a href="#'.$u->getHXLFragment($section).'">'.$section->get("dc:title").'</a></li> | ';
}
?>	
</p>

<?php 

foreach($sections as $section){
		
	print '<h2 class="vocabsection" id="'.$u->getHXLFragment($section).'">'.$section->get("dc:title").'</h2>';
	print '<p>'.$section->get("rdfs:comment").'</p>';
	print '	<p>Alphabetical index of all classes and properties in the '.$section->get("dc:title").':</p>';

// create and show graph:

// create the dot graph:

$file = $u->getHXLFragment($section).'.dot';

$dot = 'digraph { 
 rankdir="BT";	
 charset="utf-8";
 overlap=false;
 edge [color=darkslategray];
 edge [fontname=Helvetica];
 node [fontname=Helvetica];

';



//subclass links:
foreach($classes as $class){
	if($class->get("rdfs:isDefinedBy") == $section){	
		// create a node for this class, so that the figure also contains all 'disconnected' classes
		$dot .= '"' . $u->getHXLFragment($class) . '" [ URL = "./#' . $u->getHXLFragment($class) . '"] ;
		';
		
		$superclasses = $class->all("rdfs:subClassOf");
		
		foreach($superclasses as $super){
			if($super->get("rdfs:isDefinedBy") != $section){	
				$dot .= '"' . $u->getHXLFragment($super) . '" [ URL = "./#' . $u->getHXLFragment($class) . '" color="gray" fontcolor="gray"] ;
				';
			}
			$dot .= '"' . $u->getHXLFragment($class) . '" -> "' . $u->getHXLFragment($super) . '" [ color=red style="dashed" ]; 
			' ;
		}
	}
}

// domain -> range links
foreach($properties as $property){
	if($property->get("rdfs:isDefinedBy") == $section){	
		// get all domain / range combinations and print a triple for each:
		
		$domains = $property->all("rdfs:domain");
		$ranges = $property->all("rdfs:range");
		
		foreach($domains as $domain){
			// if the domain or range are define in another section, mark them grey:
			if($domain->get("rdfs:isDefinedBy") != $section){
				$dot .= '"' . $u->getHXLFragment($domain) . '" [ URL = "./#' . $u->getHXLFragment($domain) . '" color="gray" fontcolor="gray"] ;
				';
			}
			foreach($ranges as $range){
				if($range->get("rdfs:isDefinedBy") != $section){
					$dot .= '"' . $u->getHXLFragment($range) . '" [ URL = "./#' . $u->getHXLFragment($range) . '" color="gray" fontcolor="gray"] ;
					';
				}
				
				$dot .= '"' . $u->getHXLFragment($domain) . '" -> "' . $u->getHXLFragment($range) . '" [ label="' . $u->getHXLFragment($property) . '" URL = "./#' . $u->getHXLFragment($property) . '"]; 
				' ;
			}
		}
	}
}

$dot .= '}';

file_put_contents($file, $dot);

?>

<div class="hero-unit" style="padding: 15px">

<p><strong>Classes</strong> |
  

<?php 
	// index of all classes in alphabetical order:
	
	foreach($classes as $class){
		if($class->get("rdfs:isDefinedBy") == $section){
			print $u->makeLink($class).' | ' ;
		}
	}
	
?>
  
</p>

</div>

<div class="hero-unit" style="padding: 15px">

<p><strong>Properties</strong> |

<?php 
	// index of all properties in alphabetical order:
	
	foreach($properties as $property){
		if($property->get("rdfs:isDefinedBy") == $section){
			print $u->makeLink($property).' | ' ;
		}
	}
?>
</p>

</div>

<?php

// show graph img:
print '<p>Extended graph visualization of the '.$section->get("dc:title").', including adjacent classes defined in a different section (in grey).</p><a href="'.$u->getHXLFragment($section).'.dot.svg" target="_blank"><img src="'.$u->getHXLFragment($section).'.dot.png"></a>';
print '<p align="right"><small>[click to enlarge as <a href="'.$u->getHXLFragment($section).'.dot.svg" target="_blank">SVG (with embedded hyperlinks)</a> or <a href="'.$u->getHXLFragment($section).'.dot.png" target="_blank">PNG</a>]</small></p>';


?>
	<h3>Classes</h3>


<?php


	foreach($classes as $class){
		if($class->get("rdfs:isDefinedBy") == $section){
			print '		<div class="specterm" id="' .$u->getFragment($class). '" about="" typeof="'. $class->get('rdf:type') .'">';
			print '	  <h4>' . $class->label();
			if($class->hasProperty('skos:altLabel')){
				$alts = $class->all('skos:altLabel');
				foreach($alts as $alt){
					print ' / '.$alt;
				}
			}
			
			// show plural form of label, if there is one:
			if($class->get("http://www.wasab.dk/morten/2004/03/label#plural")){
				print ' <small><em>Plural: '.$class->get("http://www.wasab.dk/morten/2004/03/label#plural").'.</em></small>' ;
			}
			
			print '</h4>';
			
			
			
			// highlight hxl:TopLevelConcepts
			if($class->get($vocab."#topLevelConcept")){
				print '<p><strong>'.$class->label().' is a HXL top level concept.</strong></p>' ;
			}
					
			
			
			if($class->hasProperty("rdfs:subClassOf")){	 
				print'	  <div rel="rdfs:subClassOf" resource="'.$class->get('rdfs:subClassOf').'"></div>';
			}
			
			print'	  <div property="skos:prefLabel" content="' . $class->label() . '" xml:lang="en"></div>';
			print'	  <p class="TermComment" property="rdfs:comment" xml:lang="en">Class. ' . $class->get('rdfs:comment') . '</p>';
			print'  <table class="table table-bordered">
			    <tbody>
			      <tr><th>Identifier:</th><td>'.$u->makeIDLink($class).'</td></tr>';
			if($class->hasProperty("rdfs:subClassOf") && $u->getFragment($class->get('rdfs:subClassOf')) != "BaseClass"){	 
				print'	 <tr><th>Subclass of:</th><td>'.$u->makeLink($class->get('rdfs:subClassOf')).'</td></tr>';
			}
			
			// find all subclasses, and properties where this resource is domain or range:
			$subclasses = $graph->resourcesMatching('rdfs:subClassOf', $class);
			
				if(count($subclasses) > 0){
					sort($subclasses);
					print'	 <tr><th>Subclasses:</th><td>';
			
					foreach($subclasses as $subclass){
						print $u->makeLink($subclass).' | ';
					}
					print'</td>';
				}
				
				$u->getDomains($graph, $class);	
				$u->getRanges($graph, $class);	
			
				print '	      </tbody>
			    	</table>
			  	</div>';
			
			}
	}


?>



<h3>Properties</h3>

<?php

	foreach($properties as $property){
		if($property->get("rdfs:isDefinedBy") == $section){
				
			print '		<div class="specterm" id="' .$u->getFragment($property). '" about="" typeof="'. $property->get('rdf:type') .'">';
			
			print '  <h4>'.$property->label().'</h4>';
			
			print '  <p class="TermComment" property="rdfs:comment" xml:lang="en">Property. '.$property->get('rdfs:comment').'</p>';
			print '  <table class="table table-bordered">';
			print '    <tbody>';
			print '     <tr><th>Identifier:</th><td>'.$u->makeIDLink($property).'</td></tr>';
			
			print '      <tr><th>Domain:</th>';
			print '	  <td>';
			
			$domains = $property->all('rdfs:domain');
			foreach($domains as $domain){
			 	print	$u->makeLink($domain).'<span rel="rdfs:domain" resource="'.$domain.'"></span> ';
			}
	
			print '</td>';
			print '	  </tr>';
			print '      <tr>';
			print '	  <th>Range:</th>';
			print '	  <td>';
	
			$ranges = $property->all('rdfs:range');
			foreach($ranges as $range){
	 			print	$u->makeLink($range).'<span rel="rdfs:range" resource="'.$range.'"></span> ';
			}
	
			print ' </td>';
			print '	  </tr>';
			
			// check for inverse property
			$inverse = $graph->resourcesMatching('owl:inverseOf', $property);
			if(count($inverse) > 0){
				print '  <tr>';
				print '	  <th>Inverse property:</th>';
				print '	  <td>';
				print $u->getHXLFragment($inverse[0]).'<span rel="owl:inverseOf" resource="'.$inverse[0].'"></span> ';
				print '   </td>';
				print '	 </tr>';
				
			}
			print '      </tbody>';
			print '    </table>';
			print '</div>';
		}
	}

}
?>

</div>

<h2 id="sec-completegraph">Integrated graph overview of the complete HXL vocabulary</h2>

<p>The following figure gives an overview of <em>all</em> classes and properties defined by HXL, and how they are connected. The ellipses represent classes, whereas the arrows represent properties: Dashed lines indicate a subclass relationship  (e.g. <a href="#Emergency">Emergency</a> is a  subclass of <a href="#Situation">Situation</a>). Solid lines are labeled with the property that connects these two classes.</p>

<a href="hxl.dot.svg" target="_blank"><img src="hxl.dot.png"></a>
<p align="right"><small>[click to enlarge as <a href="hxl.dot.svg" target="_blank">SVG (with embedded hyperlinks)</a> or <a href="hxl.dot.png" target="_blank">PNG</a>]</small></p>


<h2 id="sec-license">License</h2>
<p><?php print $vocab->get("dc:license"); ?></p>

<p about="" resource="http://www.w3.org/TR/rdfa-syntax" rel="dc:conformsTo">
  <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml-rdfa.png" style="border: 0pt none; float: right" alt="Valid XHTML + RDFa" /> </a>
  This Vocabulary Specification relies on W3C's <a href="http://www.w3.org/RDF/">RDF</a> technology, an open Web standard that can be freely used by anyone.</p>

  <p>This visual layout and structure of the specification was adapted from the <a href="http://open-biomed.sourceforge.net/opmv/ns.html">Open Provenance Model Vocabulary</a> edited by Jun Zhao, <a href="http://rdfs.org/sioc/spec/">SIOC Core Ontology Specification</a> edited by Uldis Bojars and John G. Breslin and the <a href="http://trdf.sourceforge.net/provenance/ns.html">Provenance Vocabulary Core Ontology Specification</a> edited by Olaf Hartig and Jun Zhao.</p>



<h2 id="sec-changes">Change log</h2>

<p><?php print $vocab->get('skos:changeNote'); ?></p>

<?php

// create the dot graph:

$file = 'hxl.dot';

$dot = 'digraph { 
 rankdir="BT";
 charset="utf-8";
 overlap=false;
 edge [color=darkslategray];
 edge [fontname=Helvetica];
 node [fontname=Helvetica];

';



//subclass links:
foreach($classes as $class){
		
	// create a node for this class, so that the figure also contains all 'disconnected' classes
	$dot .= '"' . $u->getHXLFragment($class) . '" [ URL = "./#' . $u->getHXLFragment($class) . '"] ;
	';
	
	$superclasses = $class->all("rdfs:subClassOf");
	
	foreach($superclasses as $super){
		$dot .= '"' . $u->getHXLFragment($class) . '" -> "' . $u->getHXLFragment($super) . '" [ color=red style="dashed" ]; 
		' ;
	}
	
}

// domain -> range links
foreach($properties as $property){
	
	// get all domain / range combinations and print a triple for each:
	
	$domains = $property->all("rdfs:domain");
	$ranges = $property->all("rdfs:range");
	
	foreach($domains as $domain){
		foreach($ranges as $range){
			$dot .= '"' . $u->getHXLFragment($domain) . '" -> "' . $u->getHXLFragment($range) . '" [ label="' . $u->getHXLFragment($property) . '" URL = "./#' . $u->getHXLFragment($property) . '"]; 
			' ;
		}
	}
	
}

$dot .= '}';

file_put_contents($file, $dot);

?>

			</div>
	  	</div>
	</div>

  </div> <!-- /container -->
	
  <div class="container footer">
		<div class="row">
		  <div class="span3"><strong>Contact</strong><br />
		  This site is part of the HumanitarianResponse network. Write to 
		  <a href="mailto:info@humanitarianresponse.info">info@humanitarianresponse.info</a> for more information.</div>
		  <div class="span3"><strong>Updates</strong><br />
		  This part of the docs has been last updated on <strong> Nov 8, 2012</strong> by <a href="http://carsten.io">Carsten Keßler</a>.
      </div>
      <div class="span3"><strong>Elsewhere</strong><br />
      The entire code for HXL and the tools we are building around the standard is available on <a href="https://github.com/hxl-team">GitHub</a>.</div>      
		  <div class="span3"><strong>Legal</strong><br />
		  &copy; 2012 UNOCHA</div>
		</div>
	</div>
	  <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="js/bootstrap.min.js"></script> 
    <script src="js/prettify.js"></script>
  </body>
</html>

<?php
// supports logging of var_dump to error_log; use like this:
// ob_start("var_log");
// var_dump($variable);
// ob_end_flush();
function var_log($buffer) {
  error_log ($buffer);
}
?>