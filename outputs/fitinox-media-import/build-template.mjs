import { fileURLToPath } from "node:url";
import fs from "node:fs/promises";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const source = "/Users/user/Downloads/Excel subida masiva productos.xlsx";
const output = fileURLToPath(new URL("../../outputs/fitinox-media-import/Plantilla_importacion_productos_FITINOX.xlsx", import.meta.url));
const storage = fileURLToPath(new URL("../../storage/app/private/templates/Plantilla_importacion_masiva_productos_FITINOX.xlsx", import.meta.url));

const workbook = await SpreadsheetFile.importXlsx(await FileBlob.load(source));
const sheet = workbook.worksheets.getItem("Hoja 1");
sheet.freezePanes.freezeRows(1);
sheet.getRange("K82").values = [[null]];
sheet.getRange("K94").values = [[null]];
sheet.getRange("K114").values = [[null]];

const exported = await SpreadsheetFile.exportXlsx(workbook);
await exported.save(output);
await fs.copyFile(output, storage);

console.log((await workbook.inspect({ kind: "region", sheetId: "Hoja 1", range: "A1:M12", maxChars: 8000 })).ndjson);
console.log((await workbook.inspect({ kind: "match", searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A", options: { useRegex: true, maxResults: 300 }, summary: "final formula error scan" })).ndjson);
const preview = await workbook.render({ sheetName: "Hoja 1", range: "A1:M25", scale: 1.5, format: "png" });
await fs.writeFile(fileURLToPath(new URL("../../outputs/fitinox-media-import/template-preview.png", import.meta.url)), new Uint8Array(await preview.arrayBuffer()));
