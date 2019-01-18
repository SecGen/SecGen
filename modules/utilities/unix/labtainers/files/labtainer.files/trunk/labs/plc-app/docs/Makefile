plc-app.pdf: plc-app.tex header.tex plc-app.jpg
	latex plc-app
	pdflatex -jobname=plc-app plc-app
	pdflatex -jobname=plc-app plc-app

clean:
	rm -fr auto
	rm -f plc-app.aux plc-app.log plc-app.pdf plc-app.dvi plc-app.out
