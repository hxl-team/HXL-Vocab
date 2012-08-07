REM Convert ttl to other serializations so these can be available for download
rapper -i turtle hxl.ttl -o rdfxml > hxl.rdf
rapper -i turtle hxl.ttl -o ntriples > hxl.n3
REM Run the php to generate the dot files (which describe the graphs in code).  This outputs to file only to suppress the echo.
curl -o index.html http://localhost/HXL-Vocab/Tools/index.php
REM convert the dot files to the PNG and SVG representations of the graphs that are used on the web page
for %%x in (*.dot) do (dot %%x -Tpng -O) 
for %%x in (*.dot) do (dot %%x -Tsvg -O) 
REM overwrite index.html to get final static version of the web page
curl -o index.html http://localhost/HXL-Vocab/Tools/index.php
REM copy the source file to the static folder which is where the server reads the web page from.  This leaves behind a copy of hxl.ttl for future edits.
copy hxl.ttl static\hxl.ttl
REM move the remaining necessary files (which will be regenerated next time this script is run) to the static folder where they will be accessed when index.html is loaded.
robocopy . static *.png *.svg *.pdf *.html *.css *.rdf *.n3 /MOV

