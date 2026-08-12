import fs from "node:fs/promises";
import { SpreadsheetFile, Workbook } from "/Users/user/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs";

const outputDir = "/Users/user/Desktop/FITINOX/outputs/fitinox-importacion-masiva";
const storageDir = "/Users/user/Desktop/FITINOX/storage/app/private/templates";
const filename = "Plantilla_importacion_masiva_productos_FITINOX.xlsx";

await fs.mkdir(outputDir, { recursive: true });
await fs.mkdir(storageDir, { recursive: true });

const workbook = Workbook.create();
const template = workbook.worksheets.add("PLANTILLA_IMPORTACION");
const instructions = workbook.worksheets.add("INSTRUCCIONES");
const example = workbook.worksheets.add("EJEMPLO_COMPLETO");

const navy = "#123044";
const blue = "#1E5AA8";
const paleBlue = "#EAF2F8";
const paleGreen = "#EAF7F0";
const paleYellow = "#FFF4CC";
const border = "#CFD8E3";
const text = "#183247";
const muted = "#607284";
const white = "#FFFFFF";

const headers = [[
  "CODIGO", "DETALLE", "CATEGORIA", "SUBCATEGORIA", "TUBO", "ROSCA", "CALIDAD",
  "FOTO", "VIDEO", "PRECIO_LISTA_US", "DESCUENTO", "PRECIO_FINAL_US",
  "STOCK_FISICO", "STOCK_EN_TRANSITO", "CONTROL"
]];

template.showGridLines = false;
template.getRange("A1:O1").merge();
template.getRange("A1").values = [["FITINOX · IMPORTACIÓN MASIVA DE PRODUCTOS"]];
template.getRange("A1:O1").format = {
  fill: navy,
  font: { bold: true, color: white, size: 18 },
  verticalAlignment: "center",
  rowHeight: 38,
};
template.getRange("A2:O2").merge();
template.getRange("A2").values = [["Completá una fila por cada código o variante. No cambies los nombres de las columnas. Leé la hoja INSTRUCCIONES antes de cargar."]];
template.getRange("A2:O2").format = {
  fill: paleBlue,
  font: { color: text, italic: true, size: 11 },
  wrapText: true,
  verticalAlignment: "center",
  rowHeight: 34,
};
template.getRange("A4:O4").values = headers;
template.getRange("A4:O4").format = {
  fill: blue,
  font: { bold: true, color: white, size: 10 },
  horizontalAlignment: "center",
  verticalAlignment: "center",
  wrapText: true,
  rowHeight: 42,
  borders: { preset: "all", style: "thin", color: border },
};

template.getRange("A5:O204").format = {
  font: { color: text, size: 10 },
  borders: { preset: "all", style: "thin", color: border },
  verticalAlignment: "center",
};
template.getRange("C5:D204").format.fill = paleBlue;
template.getRange("H5:I204").format.fill = paleGreen;
template.getRange("L5").formulas = [["=IF(OR(J5=\"\",K5=\"\"),\"\",ROUND(J5*(1-K5/100),2))"]];
template.getRange("L5:L204").fillDown();
template.getRange("O5").formulas = [["=IF(AND(A5=\"\",B5=\"\",C5=\"\",D5=\"\",E5=\"\",F5=\"\",G5=\"\",H5=\"\",I5=\"\",J5=\"\",K5=\"\",M5=\"\",N5=\"\"),\"\",IF(OR(A5=\"\",B5=\"\",C5=\"\",E5=\"\",F5=\"\"),\"FALTAN DATOS\",\"LISTO\"))"]];
template.getRange("O5:O204").fillDown();
template.getRange("O5:O204").format.fill = paleYellow;

template.getRange("J5:L204").setNumberFormat("0.00");
template.getRange("K5:K204").setNumberFormat("0.00");
template.getRange("M5:N204").setNumberFormat("0");
template.getRange("A5:I204").setNumberFormat("@");
template.dataValidations.add({ range: "K5:K204", rule: { type: "decimal", operator: "between", formula1: 0, formula2: 100 } });
template.dataValidations.add({ range: "M5:N204", rule: { type: "whole", operator: "greaterThanOrEqual", formula1: 0 } });
template.getRange("O5:O204").conditionalFormats.add("containsText", { text: "LISTO", format: { fill: "#DDF5E8", font: { color: "#087443", bold: true } } });
template.getRange("O5:O204").conditionalFormats.add("containsText", { text: "FALTAN", format: { fill: "#FDE8E8", font: { color: "#A51D2D", bold: true } } });
template.freezePanes.freezeRows(4);

const widths = [18, 24, 25, 22, 12, 12, 14, 29, 29, 18, 13, 18, 15, 18, 17];
widths.forEach((width, index) => {
  template.getRangeByIndexes(0, index, 204, 1).format.columnWidth = width;
});

instructions.showGridLines = false;
instructions.getRange("A1:F1").merge();
instructions.getRange("A1").values = [["CÓMO COMPLETAR LA PLANTILLA"]];
instructions.getRange("A1:F1").format = {
  fill: navy,
  font: { bold: true, color: white, size: 18 },
  rowHeight: 38,
};
instructions.getRange("A3:F3").values = [["PASO", "ACCIÓN", "QUÉ DEBE HACER", "EJEMPLO", "OBLIGATORIO", "IMPORTANTE"]];
instructions.getRange("A3:F3").format = {
  fill: blue,
  font: { bold: true, color: white },
  horizontalAlignment: "center",
  verticalAlignment: "center",
  wrapText: true,
  rowHeight: 32,
  borders: { preset: "all", style: "thin", color: border },
};
instructions.getRange("A4:F10").values = [
  [1, "Una fila por código", "Cada variante o medida debe ocupar una fila. Las filas con el mismo DETALLE se agrupan como un producto.", "Recto Macho con distintos TUBO y ROSCA", "Sí", "CODIGO no puede repetirse."],
  [2, "Categoría", "Escribí la categoría principal y, si corresponde, la subcategoría. El sistema las crea si todavía no existen.", "Accesorios para tubos / Rectos macho", "CATEGORIA: sí", "Usá siempre exactamente el mismo nombre."],
  [3, "Foto", "Escribí el nombre exacto del archivo, incluida su extensión. Subí luego ese archivo a products/images.", "1-Recto-Macho-F.jpg", "No", "No escribir solo ‘1-Recto Macho F’. Debe incluir .jpg, .png o .webp."],
  [4, "Video", "Usá el nombre exacto del archivo con extensión o pegá una URL completa de YouTube.", "1-Recto-Macho-V.mp4", "No", "Los archivos se suben a products/videos. YouTube no requiere archivo."],
  [5, "Precios", "Ingresá PRECIO_LISTA_US y DESCUENTO. PRECIO_FINAL_US se calcula automáticamente.", "14,69 y 5 → 13,96", "No", "DESCUENTO se escribe como 5, no como 0,05."],
  [6, "Stock", "Ingresá números enteros iguales o mayores que cero.", "STOCK_FISICO 8 / STOCK_EN_TRANSITO 3", "No", "Dejá vacío si todavía no hay información."],
  [7, "Revisión", "Antes de importar, verificá que CONTROL diga LISTO en todas las filas cargadas.", "LISTO", "Sí", "No borres fórmulas de PRECIO_FINAL_US ni CONTROL."],
];
instructions.getRange("A4:F10").format = {
  font: { color: text, size: 10 },
  wrapText: true,
  verticalAlignment: "top",
  borders: { preset: "all", style: "thin", color: border },
  rowHeight: 58,
};
instructions.getRange("A4:A10").format = { fill: paleBlue, font: { bold: true, color: blue, size: 14 }, horizontalAlignment: "center", verticalAlignment: "center", borders: { preset: "all", style: "thin", color: border } };
instructions.getRange("A12:F12").merge();
instructions.getRange("A12").values = [["FLUJO DE ARCHIVOS MULTIMEDIA"]];
instructions.getRange("A12:F12").format = { fill: navy, font: { bold: true, color: white, size: 13 }, rowHeight: 28 };
instructions.getRange("A13:F15").values = [
  ["1", "Renombrar", "Usar nombres únicos y simples, sin caracteres extraños.", "1-Recto-Macho-F.jpg", "", ""],
  ["2", "Completar Excel", "Copiar ese nombre exacto en FOTO o VIDEO.", "Debe coincidir letra por letra.", "", ""],
  ["3", "Subir archivos", "Fotos → storage/app/public/products/images · Videos → storage/app/public/products/videos", "Después importar el Excel.", "", ""],
];
instructions.getRange("A13:F15").format = { fill: paleGreen, font: { color: text, size: 10 }, wrapText: true, verticalAlignment: "center", borders: { preset: "all", style: "thin", color: border }, rowHeight: 44 };
[9, 18, 43, 30, 14, 37].forEach((width, index) => instructions.getRangeByIndexes(0, index, 15, 1).format.columnWidth = width);
instructions.freezePanes.freezeRows(3);

example.showGridLines = false;
example.getRange("A1:O1").merge();
example.getRange("A1").values = [["EJEMPLO COMPLETO · SOLO COMO REFERENCIA, NO SE IMPORTA"]];
example.getRange("A1:O1").format = { fill: navy, font: { bold: true, color: white, size: 16 }, rowHeight: 36 };
example.getRange("A3:O3").values = headers;
example.getRange("A3:O3").format = { fill: blue, font: { bold: true, color: white, size: 10 }, horizontalAlignment: "center", verticalAlignment: "center", wrapText: true, rowHeight: 42, borders: { preset: "all", style: "thin", color: border } };
example.getRange("A4:O7").values = [
  ["AL-FSS-200-1-2", "Recto Macho", "Accesorios para tubos", "Rectos macho", "1/8\"", "1/8\"", "AISI 316", "1-Recto-Macho-F.jpg", "1-Recto-Macho-V.mp4", 14.69, 5, 13.96, 8, 3, "LISTO"],
  ["AL-FSS-200-1-4", "Recto Macho", "Accesorios para tubos", "Rectos macho", "1/8\"", "1/4\"", "AISI 316", "1-Recto-Macho-F.jpg", "1-Recto-Macho-V.mp4", 15.11, 5, 14.35, 4, 2, "LISTO"],
  ["AL-FSS-400-1-2", "Recto Macho", "Accesorios para tubos", "Rectos macho", "1/4\"", "1/8\"", "AISI 316", "1-Recto-Macho-F.jpg", "https://youtu.be/ejemplo", 13.33, 5, 12.66, 10, 0, "LISTO"],
  ["AL-CODO-001", "Codo 90°", "Accesorios para tubos", "Codos", "1/4\"", "1/4\"", "AISI 316", "Codo-90-F.webp", "", 20.00, 10, 18.00, 6, 1, "LISTO"],
];
example.getRange("A4:O7").format = { font: { color: text, size: 10 }, verticalAlignment: "center", borders: { preset: "all", style: "thin", color: border }, rowHeight: 30 };
example.getRange("C4:D7").format.fill = paleBlue;
example.getRange("H4:I7").format.fill = paleGreen;
example.getRange("J4:L7").setNumberFormat("0.00");
example.getRange("O4:O7").format = { fill: "#DDF5E8", font: { color: "#087443", bold: true }, horizontalAlignment: "center", borders: { preset: "all", style: "thin", color: border } };
widths.forEach((width, index) => example.getRangeByIndexes(0, index, 7, 1).format.columnWidth = width);
example.freezePanes.freezeRows(3);

const outPath = `${outputDir}/${filename}`;
const storagePath = `${storageDir}/${filename}`;
const xlsx = await SpreadsheetFile.exportXlsx(workbook);
await xlsx.save(outPath);
await fs.copyFile(outPath, storagePath);

for (const sheetName of ["PLANTILLA_IMPORTACION", "INSTRUCCIONES", "EJEMPLO_COMPLETO"]) {
  const preview = await workbook.render({ sheetName, autoCrop: "all", scale: 1, format: "png" });
  await fs.writeFile(`${outputDir}/preview-${sheetName}.png`, new Uint8Array(await preview.arrayBuffer()));
}

const inspection = await workbook.inspect({
  kind: "region",
  sheetId: "PLANTILLA_IMPORTACION",
  range: "A1:O8",
  maxChars: 7000,
});
console.log(inspection.ndjson);
console.log(JSON.stringify({ outPath, storagePath }));
