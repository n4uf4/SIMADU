/**
 * Stitch Screen Fetcher v2
 * Fetches HTML and screenshot for each screen in the project.
 */

import { StitchToolClient } from "@google/stitch-sdk";
import { writeFile, mkdir } from "fs/promises";
import { createWriteStream } from "fs";
import https from "https";
import http from "http";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR = path.join(__dirname, "stitch_screens");

const PROJECT_ID = "6425105726239395289";

const SCREENS = [
  { name: "buat_pengaduan",       id: "303c38ac0e7f4d19b6ef7daf8e721471" },
  { name: "detail_laporan_admin", id: "e25b9e1faf0e419c9f9356989cc3e080" },
  { name: "riwayat_laporan",      id: "c976f85fe0ef4f83afaf4347b6f00ce4" },
  { name: "login_mahasiswa",      id: "9024b728448045d5bbe082acd40b31d9" },
  { name: "dashboard_admin",      id: "38c20ae4d49f41b2aa45c2b18e5838ea" },
];

function downloadFile(url, dest, redirectCount = 0) {
  return new Promise((resolve, reject) => {
    if (redirectCount > 10) return reject(new Error("Too many redirects"));
    const proto = url.startsWith("https") ? https : http;
    const file = createWriteStream(dest);
    proto.get(url, (res) => {
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        file.close();
        return downloadFile(res.headers.location, dest, redirectCount + 1)
          .then(resolve).catch(reject);
      }
      if (res.statusCode !== 200) {
        file.close();
        return reject(new Error(`HTTP ${res.statusCode}`));
      }
      res.pipe(file);
      file.on("finish", () => file.close(resolve));
    }).on("error", (err) => {
      file.close();
      reject(err);
    });
  });
}

async function main() {
  const apiKey = process.env.STITCH_API_KEY;
  if (!apiKey) {
    console.error("ERROR: STITCH_API_KEY not set.");
    process.exit(1);
  }

  await mkdir(OUT_DIR, { recursive: true });
  const client = new StitchToolClient({ apiKey });
  const results = [];

  for (const screen of SCREENS) {
    console.log(`\n[${screen.name}] Fetching...`);
    try {
      const result = await client.callTool("get_screen", {
        project_id: PROJECT_ID,
        screen_id: screen.id,
      });

      const htmlUrl  = result?.htmlCode?.downloadUrl  || null;
      const imageUrl = result?.screenshot?.downloadUrl || null;
      const title    = result?.title || screen.name;

      console.log(`  Title: ${title}`);
      console.log(`  HTML URL:  ${htmlUrl ? "✓ found" : "✗ missing"}`);
      console.log(`  Image URL: ${imageUrl ? "✓ found" : "✗ missing"}`);

      // Save raw JSON
      await writeFile(
        path.join(OUT_DIR, `${screen.name}_result.json`),
        JSON.stringify(result, null, 2)
      );

      // Download HTML
      if (htmlUrl) {
        const htmlFile = path.join(OUT_DIR, `${screen.name}.html`);
        await downloadFile(htmlUrl, htmlFile);
        console.log(`  ✓ HTML  → ${htmlFile}`);
      }

      // Download screenshot
      if (imageUrl) {
        const imgFile = path.join(OUT_DIR, `${screen.name}.png`);
        await downloadFile(imageUrl, imgFile);
        console.log(`  ✓ Image → ${imgFile}`);
      }

      results.push({ screen: screen.name, title, htmlUrl, imageUrl, ok: true });
    } catch (err) {
      console.error(`  ERROR: ${err.message}`);
      results.push({ screen: screen.name, id: screen.id, error: err.message, ok: false });
    }
  }

  await client.close();

  console.log("\n\n=== DONE ===");
  for (const r of results) {
    const status = r.ok ? "✓" : "✗";
    console.log(`${status} ${r.screen}: ${r.title || r.error}`);
  }

  await writeFile(path.join(OUT_DIR, "summary.json"), JSON.stringify(results, null, 2));
  console.log(`\nFiles saved to: ${OUT_DIR}`);
}

main().catch((err) => {
  console.error("Fatal:", err);
  process.exit(1);
});
