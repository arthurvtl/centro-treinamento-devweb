#!/usr/bin/env bash
# Gera diagramas (Graphviz), HTML, PDF (Chrome headless) e DOCX (pandoc)
# a partir de documento-analise.md.
# Requisitos: graphviz (dot), pandoc, Google Chrome.
set -euo pipefail
cd "$(dirname "$0")"

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
OUT="Documento de Analise de Negocio - CT.pdf"

echo "› diagramas"
for f in classes der; do
  dot -Tsvg "diagramas/$f.dot" -o "diagramas/$f.svg"
  dot -Tpng -Gdpi=200 "diagramas/$f.dot" -o "diagramas/$f.png"
done

echo "› html"
pandoc documento-analise.md -f markdown -t html5 --standalone \
  --css estilo.css --embed-resources --metadata title="Documento de Análise de Negócio — CT" \
  -o documento-analise.html

echo "› pdf"
"$CHROME" --headless=new --disable-gpu --no-pdf-header-footer \
  --print-to-pdf="$PWD/$OUT" "file://$PWD/documento-analise.html" 2>/dev/null

echo "› docx"
pandoc documento-analise.md -f markdown -t docx -o "Documento de Analise de Negocio - CT.docx"

echo "ok → $OUT"
