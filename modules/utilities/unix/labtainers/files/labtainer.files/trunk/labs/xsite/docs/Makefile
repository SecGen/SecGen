xsite.pdf: Web_XSS_Elgg.tex Web_Environment_PartI_Elgg.tex header.tex
	latex Web_XSS_Elgg
	pdflatex -jobname=xsite Web_XSS_Elgg
	pdflatex -jobname=xsite Web_XSS_Elgg

clean:
	rm -fr auto
	rm -f Web_XSS_Elgg.aux Web_XSS_Elgg.log Web_XSS_Elgg.pdf
