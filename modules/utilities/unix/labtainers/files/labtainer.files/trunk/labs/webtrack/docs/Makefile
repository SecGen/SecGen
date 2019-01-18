webtrack.pdf: Web_Tracking_Elgg.tex Web_Environment_PartI_Elgg.tex header.tex
	latex Web_Tracking_Elgg
	pdflatex -jobname=webtrack Web_Tracking_Elgg
	pdflatex -jobname=webtrack Web_Tracking_Elgg

clean:
	rm -fr auto
	rm -f Web_Tracking_Elgg.aux Web_Tracking_Elgg.log Web_Tracking_Elgg.pdf
