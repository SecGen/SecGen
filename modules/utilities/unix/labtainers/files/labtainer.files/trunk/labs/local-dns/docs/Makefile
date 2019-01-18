local-dns.pdf: DNS_Local.tex header.tex
	latex DNS_Local
	pdflatex -jobname=local-dns DNS_Local
	pdflatex -jobname=local-dns DNS_Local

clean:
	rm -fr auto
	rm -f DNS_Local.aux DNS_Local.log local-dns.pdf
