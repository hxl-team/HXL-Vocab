rm /Applications/MAMP/logs/php_error.log;
touch /Applications/MAMP/logs/php_error.log;
rapper -i turtle hxl.ttl -o rdfxml > hxl.rdf; 
rapper -i turtle hxl.ttl -o ntriples > hxl.n3; 
curl -o index.html http://localhost/HXL-Vocab/Tools/index.php; 
for file in *.dot; do
	dot $file -Tpng -O; 
	dot $file -Tsvg -O; 
done
curl -o index.html http://localhost/HXL-Vocab/Tools/index.php; 
scp hxl.ttl static/hxl.ttl;
mv *.png *.svg *.pdf *.html *.css *.rdf *.n3 static;
open http://localhost/HXL-Vocab/Tools/static/index.html#sec-toc;
