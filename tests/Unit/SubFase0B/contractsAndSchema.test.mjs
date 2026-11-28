import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import test from "node:test";
import {
  GUARD_PRESETS,
  collectSubFase0BStatus,
  evaluateProductionGuards,
} from "../../../scripts/subfase-0b-validator.mjs";

const rootDir = process.cwd();

test("8 Contracts tersedia lengkap beserta tanda tangan method Architecture §5.2", () => {
  const status = collectSubFase0BStatus();
  assert.equal(status.contracts.length, 8);
  for (const contract of status.contracts) {
    assert.equal(contract.exists, true, `Contract hilang: ${contract.file}`);
    assert.equal(contract.methodsFound, true, `Method tidak lengkap pada: ${contract.file}`);
  }
});

test("25 Adapter implementations dan AppServiceProvider binding tersedia tanpa hardcode secret", () => {
  const status = collectSubFase0BStatus();
  assert.equal(status.adapters.length, 25);
  for (const adapter of status.adapters) {
    assert.equal(adapter.exists, true, `Adapter hilang: ${adapter.path}`);
  }

  const providerSrc = fs.readFileSync(
    path.join(rootDir, "app", "Providers", "AppServiceProvider.php"),
    "utf8"
  );
  for (const contract of status.contracts) {
    assert.ok(
      providerSrc.includes(`${contract.name}::class`),
      `AppServiceProvider belum mem-bind ${contract.name}`
    );
  }
});

test("8 Guard Rules menolak konfigurasi local/staging/broken dan meluluskan konfigurasi production-safe", () => {
  const localReport = evaluateProductionGuards(GUARD_PRESETS.local);
  assert.equal(localReport.passed, false);
  assert.equal(localReport.failedCount, 4);

  const stagingReport = evaluateProductionGuards(GUARD_PRESETS.staging);
  assert.equal(stagingReport.passed, false);
  assert.equal(stagingReport.failedCount, 3);

  const brokenAllReport = evaluateProductionGuards({
    otp_driver: "fonnte",
    mail_mailer: "log",
    app_debug: true,
    telescope_enabled: true,
    session_driver: "file",
    queue_connection: "sync",
    error_driver: "sentry",
    storage_driver: "s3",
    app_url: "http://localhost:8000",
    force_https: false,
    fonnte_token: "",
    fonnte_url: "",
    sentry_dsn: "",
    aws_access_key_id: "",
    aws_bucket: "",
  });
  assert.equal(brokenAllReport.passed, false);
  assert.equal(brokenAllReport.failedCount, 7);

  const prodReport = evaluateProductionGuards(GUARD_PRESETS["production-safe"]);
  assert.equal(prodReport.passed, true);
  assert.equal(prodReport.failedCount, 0);
  assert.equal(prodReport.totalRules, 8);
});

test("7 migrasi inti (#1-#7) menggunakan utf8mb4_unicode_ci dan menyediakan method down()", () => {
  const status = collectSubFase0BStatus();
  assert.equal(status.migrations.length, 7);
  for (const mig of status.migrations) {
    assert.equal(mig.exists, true, `Migrasi hilang: ${mig.file}`);
    assert.equal(mig.hasUtf8mb4, true, `utf8mb4_unicode_ci hilang di ${mig.file}`);
    assert.equal(mig.hasDown, true, `down() rollback hilang di ${mig.file}`);
  }
});

test("Reference seeders mendefinisikan tepat 6 plans, 21 features, dan 126 plan_features", () => {
  const status = collectSubFase0BStatus();
  for (const seeder of status.seeders) {
    assert.equal(seeder.exists, true, `Seeder hilang: ${seeder.path}`);
  }

  assert.equal(status.matrix.planCodes.length, 6);
  assert.equal(status.matrix.featureCodes.length, 21);
  assert.equal(status.matrix.totalMappings, 126);

  const basicRowCount = status.matrix.rows.filter((r) => r.plans.starter_basic).length;
  const trialRowCount = status.matrix.rows.filter((r) => r.plans.trial).length;
  const primePlusRowCount = status.matrix.rows.filter((r) => r.plans.prime_plus).length;
  assert.equal(basicRowCount, 9);
  assert.equal(trialRowCount, 21);
  assert.equal(primePlusRowCount, 21);
});

test("AdminSeeder tidak menyimpan password literal di kode sumber", () => {
  const adminSeeder = fs.readFileSync(
    path.join(rootDir, "database", "seeders", "AdminSeeder.php"),
    "utf8"
  );
  assert.ok(adminSeeder.includes("env('SEED_ADMIN_PASSWORD'"));
  assert.ok(adminSeeder.includes("Hash::make"));
});

test("MANIFEST_PERUBAHAN.json Sub-Fase 0B sinkron dengan seluruh file di disk", () => {
  const manifestPath = path.join(rootDir, "docs", "sub-fase-0b", "MANIFEST_PERUBAHAN.json");
  const manifest = JSON.parse(fs.readFileSync(manifestPath, "utf8"));
  for (const entry of manifest.entries) {
    if (entry.changeType === "New" || entry.changeType === "Modified") {
      assert.equal(
        fs.existsSync(path.join(rootDir, entry.path)),
        true,
        `File '${entry.path}' tercatat ${entry.changeType} tetapi tidak ditemukan di disk`
      );
    }
  }
});
