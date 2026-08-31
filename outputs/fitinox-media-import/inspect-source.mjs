import { fileURLToPath } from "node:url";
import fs from "node:fs/promises";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const source = "/Users/user/Downloads/Excel subida masiva productos.xlsx";
const output = fileURLToPath(new URL("../../outputs/fitinox-media-import", import.meta.url));
const workbook = await SpreadsheetFile.importXlsx(await FileBlob.load(source));

console.log((await workbook.inspect({ kind: "workbook,sheet", maxChars: 5000 })).ndjson);
const sheet = workbook.worksheets.getItem("Hoja 1");
console.log(JSON.stringify(sheet.getRange("A1:M12").values));
console.log((await workbook.inspect({ kind: "region", sheetId: "Hoja 1", range: "A1:M30", maxChars: 12000 })).ndjson);
console.log((await workbook.inspect({ kind: "formula", sheetId: "Hoja 1", range: "A1:M315", maxChars: 4000, options: { maxResults: 100 } })).ndjson);

for (const name of ["Hoja 1"]) {
  const image = await workbook.render({ sheetName: name, range: "A1:Q25", scale: 1.5, format: "png" });
  await fs.writeFile(`${output}/source-${name}.png`, new Uint8Array(await image.arrayBuffer()));
}
