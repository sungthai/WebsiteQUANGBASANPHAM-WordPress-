const fs = require("node:fs");
const path = require("node:path");

const source = path.join(__dirname, "BAO-CAO.md");
const output = path.join(__dirname, "BAO-CAO.html");
const lines = fs.readFileSync(source, "utf8").split(/\r?\n/);

function escapeHtml(value) {
  return value
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}

function inlineMarkdown(value) {
  let text = escapeHtml(value);
  text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
  text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");
  text = text.replace(/`([^`]+)`/g, "<code>$1</code>");
  return text;
}

const body = [];
let inCode = false;
let inList = false;
let inTable = false;
let cover = true;

function closeBlocks() {
  if (inList) {
    body.push("</ul>");
    inList = false;
  }
  if (inTable) {
    body.push("</table>");
    inTable = false;
  }
}

for (const line of lines) {
  if (line.startsWith("```")) {
    if (inCode) {
      body.push("</code></pre>");
    } else {
      closeBlocks();
      body.push("<pre><code>");
    }
    inCode = !inCode;
    continue;
  }
  if (inCode) {
    body.push(escapeHtml(line));
    continue;
  }
  if (line.trim() === "\\newpage") {
    closeBlocks();
    body.push("</section><section class=\"page-break\">");
    cover = false;
    continue;
  }
  if (!line.trim()) {
    closeBlocks();
    continue;
  }
  if (line.startsWith("|")) {
    if (/^\|\s*:?-{3,}/.test(line)) continue;
    if (inList) {
      body.push("</ul>");
      inList = false;
    }
    if (!inTable) {
      body.push("<table>");
      inTable = true;
    }
    const cells = line
      .replace(/^\||\|$/g, "")
      .split("|")
      .map((cell) => `<td>${inlineMarkdown(cell.trim())}</td>`)
      .join("");
    body.push(`<tr>${cells}</tr>`);
    continue;
  }
  if (inTable) {
    body.push("</table>");
    inTable = false;
  }
  const heading = line.match(/^(#{1,3})\s+(.+)$/);
  if (heading) {
    closeBlocks();
    const level = heading[1].length;
    const title = escapeHtml(heading[2]);
    body.push(`<h${level}>${title}</h${level}>`);
    continue;
  }
  const item = line.match(/^\s*[-*]\s+(.+)$/);
  if (item) {
    if (!inList) {
      body.push("<ul>");
      inList = true;
    }
    body.push(`<li>${inlineMarkdown(item[1])}</li>`);
    continue;
  }
  if (inList) {
    body.push("</ul>");
    inList = false;
  }
  if (line.startsWith(">")) {
    body.push(`<blockquote>${inlineMarkdown(line.replace(/^>\s*/, ""))}</blockquote>`);
  } else if (cover) {
    body.push(`<p class="cover-line">${inlineMarkdown(line)}</p>`);
  } else {
    body.push(`<p>${inlineMarkdown(line)}</p>`);
  }
}
closeBlocks();
body.push("</section>");

const html = `<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Báo cáo đề tài - Website quảng bá sản phẩm</title>
<style>
@page { size: A4; margin: 2.5cm 2cm 2.5cm 3cm; }
body { max-width: 17cm; margin: 0 auto; color: #111; font: 12pt/1.5 "Times New Roman", serif; }
h1, h2, h3 { page-break-after: avoid; }
h1 { font-size: 18pt; text-align: center; }
h2 { font-size: 16pt; }
h3 { font-size: 14pt; }
section:first-child { min-height: 24cm; display: flex; flex-direction: column; justify-content: center; }
.cover-line { text-align: center; margin: 0.35em 0; }
.page-break { break-before: page; page-break-before: always; }
table { width: 100%; border-collapse: collapse; font-size: 10pt; }
td, th { border: 1px solid #777; padding: 4pt; vertical-align: top; }
pre { white-space: pre-wrap; overflow-wrap: anywhere; background: #f3f4f6; padding: 8pt; font: 9pt Consolas, monospace; }
blockquote { border-left: 3px solid #9aa; padding-left: 10pt; color: #444; }
code { font-family: Consolas, monospace; }
img { max-width: 100%; height: auto; }
@media print { body { max-width: none; } a { color: inherit; text-decoration: none; } }
</style>
</head>
<body><section class="cover">${body.join("\n")}</body></html>
`;

fs.writeFileSync(output, html, "utf8");
console.log(`Created ${output}`);
console.log("Print on A4 with browser headers/footers enabled; verify the final PDF has at least 10 pages.");
