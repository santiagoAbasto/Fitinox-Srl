import { fileURLToPath } from "node:url";
import fs from "node:fs/promises";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const directory = fileURLToPath(new URL("../../outputs/fitinox-admin-download", import.meta.url));
const input = await FileBlob.load(`${directory}/Productos_FITINOX_datos_actuales.xlsx`);
const workbook = await SpreadsheetFile.importXlsx(input);

const region = await workbook.inspect({
  kind: "region",
  sheetId: "PLANTILLA_IMPORTACION",
  range: "A1:O15",
  maxChars: 8000,
});
console.log(region.ndjson);

const errors = await workbook.inspect({
  kind: "match",
  searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A",
  options: { useRegex: true, maxResults: 300 },
  summary: "final formula error scan",
});
console.log(errors.ndjson);

for (const sheetName of ["PLANTILLA_IMPORTACION", "INSTRUCCIONES", "EJEMPLO_COMPLETO"]) {
  const preview = await workbook.render({
    sheetName,
    range: sheetName === "PLANTILLA_IMPORTACION" ? "A1:O18" : undefined,
    autoCrop: "all",
    scale: 1,
    format: "png",
  });
  await fs.writeFile(`${directory}/preview-${sheetName}.png`, new Uint8Array(await preview.arrayBuffer()));
}
