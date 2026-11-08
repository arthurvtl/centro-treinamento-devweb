#!/usr/bin/env bash
# Gera diagramas (Graphviz), HTML, PDF (Chrome headless) e DOCX (pandoc)
# a partir de documento-analise.md.
# Requisitos: graphviz (dot), pandoc, python3, Google Chrome.
set -euo pipefail
cd "$(dirname "$0")"

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
PDF="Documento de Analise de Negocio - CT.pdf"
DOCX="Documento de Analise de Negocio - CT.docx"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

echo "› diagramas"
for f in classes der; do
  dot -Tsvg "diagramas/$f.dot" -o "diagramas/$f.svg"
  dot -Tpng -Gdpi=200 "diagramas/$f.dot" -o "diagramas/$f.png"
done

echo "› html"
# Sumário gerado pelo pandoc e inserido no marcador <!--SUMARIO--> (logo após a capa)
echo '$toc$' > "$TMP/so-sumario.html"
pandoc documento-analise.md -f markdown -t html5 --toc --toc-depth=2 \
  --template "$TMP/so-sumario.html" -o "$TMP/sumario.html"
pandoc documento-analise.md -f markdown -t html5 --standalone \
  --css estilo.css --embed-resources --metadata title="Documento de Análise de Negócio — CT" \
  -o documento-analise.html
python3 - "$TMP/sumario.html" <<'EOF'
import sys
sumario = open(sys.argv[1], encoding="utf-8").read()
html = open("documento-analise.html", encoding="utf-8").read()
open("documento-analise.html", "w", encoding="utf-8").write(html.replace("<!--SUMARIO-->", sumario, 1))
EOF

echo "› pdf"
"$CHROME" --headless=new --disable-gpu --no-pdf-header-footer \
  --print-to-pdf="$PWD/$PDF" "file://$PWD/documento-analise.html" 2>/dev/null

echo "› docx"
# O Word não entende a capa em HTML: ela é convertida para parágrafos no estilo "Capa"
# (definido em referencia.docx), seguida de quebra de página e de um campo de sumário.
python3 - "$TMP/docx.md" <<'EOF'
import re, sys
md = open("documento-analise.md", encoding="utf-8").read()
capa = re.search(r'<div class="capa">(.*?)</div>', md, re.S).group(1)
linhas = []
for classe, texto in re.findall(r'<p class="(\w+)">(.*?)</p>', capa, re.S):
    partes = [p.strip() for p in texto.split("<br/>")]
    if classe in ("titulo", "subtitulo"):
        partes = [f"**{p}**" for p in partes]
    linhas.append('::: {custom-style="Capa"}\n' + "\\\n".join(partes) + "\n:::\n")
quebra = '```{=openxml}\n<w:p><w:r><w:br w:type="page"/></w:r></w:p>\n```\n'
sumario = ('::: {custom-style="Capa"}\n**SUMÁRIO**\n:::\n\n```{=openxml}\n'
           '<w:p><w:r><w:fldChar w:fldCharType="begin" w:dirty="true"/></w:r>'
           '<w:r><w:instrText xml:space="preserve"> TOC \\o "1-2" \\h \\z \\u </w:instrText></w:r>'
           '<w:r><w:fldChar w:fldCharType="separate"/></w:r>'
           '<w:r><w:t>Clique com o botão direito e escolha "Atualizar campo" para gerar o sumário.</w:t></w:r>'
           '<w:r><w:fldChar w:fldCharType="end"/></w:r></w:p>\n```\n')
novo_inicio = "\n".join(linhas) + "\n" + quebra + "\n" + sumario + "\n" + quebra
md = re.sub(r'<div class="capa">.*?</div>\s*<div class="sumario">.*?</div>', lambda m: novo_inicio, md, count=1, flags=re.S)
md = re.sub(r'\A---\n.*?\n---\n', '', md, count=1, flags=re.S)  # título já está na capa
open(sys.argv[1], "w", encoding="utf-8").write(md)
EOF
pandoc "$TMP/docx.md" -f markdown -t docx --reference-doc=referencia.docx \
  --resource-path=. -M lang=pt-BR -o "$DOCX"

echo "ok → $PDF e $DOCX"
