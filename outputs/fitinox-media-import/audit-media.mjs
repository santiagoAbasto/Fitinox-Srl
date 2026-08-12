import fs from "node:fs/promises";
import path from "node:path";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const workbook = await SpreadsheetFile.importXlsx(await FileBlob.load("/Users/user/Downloads/Excel subida masiva productos.xlsx"));
const rows = workbook.worksheets.getItem("Hoja 1").getRange("I2:J315").values;
const normalize = value => String(value || "").replaceAll("°", "u").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z0-9]+/g, "");
const expectedPhotos = new Map();
const expectedVideos = new Map();
for (const [photo, video] of rows) {
  if (photo) expectedPhotos.set(normalize(photo), photo);
  if (video) expectedVideos.set(normalize(video), video);
}
const files = await fs.readdir("/Users/user/Downloads/Fotos y videos");
const actualPhotos = new Map();
const actualVideos = new Map();
for (const file of files) {
  const key = normalize(path.parse(file).name);
  if (/\.(jpe?g|png|webp)$/i.test(file)) actualPhotos.set(key, file);
  if (/\.(mp4|webm|mov)$/i.test(file)) actualVideos.set(key, file);
}
const missingPhotos = [...expectedPhotos].filter(([key]) => !actualPhotos.has(key)).map(([,value]) => value);
const missingVideos = [...expectedVideos].filter(([key]) => !actualVideos.has(key)).map(([,value]) => value);
const unusedPhotos = [...actualPhotos].filter(([key]) => !expectedPhotos.has(key)).map(([,value]) => value);
const unusedVideos = [...actualVideos].filter(([key]) => !expectedVideos.has(key)).map(([,value]) => value);
console.log(JSON.stringify({ expectedPhotos: expectedPhotos.size, actualPhotos: actualPhotos.size, missingPhotos, unusedPhotos, expectedVideos: expectedVideos.size, actualVideos: actualVideos.size, missingVideos, unusedVideos }, null, 2));
