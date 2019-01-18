xforge.pdf: Web_CSRF_Elgg.tex Web_Environment_PartI_Elgg.tex header.tex
	latex Web_CSRF_Elgg
	pdflatex -jobname=xforge Web_CSRF_Elgg
	pdflatex -jobname=xforge Web_CSRF_Elgg

clean:
	rm -fr auto
	rm -f Web_CSRF_Elgg.aux Web_CSRF_Elgg.log Web_CSRF_Elgg.pdf
