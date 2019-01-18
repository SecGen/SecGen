acl.pdf: acl.tex header.tex
	latex acl
	pdflatex -jobname=acl acl
	pdflatex -jobname=acl acl

clean:
	rm -fr auto
	rm -f acl.aux acl.log acl.pdf acl.dvi acl.out
