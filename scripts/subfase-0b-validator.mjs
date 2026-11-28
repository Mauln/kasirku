import fs from "node:fs";
import path from "node:path";

const rootDir = process.cwd();

export const REQUIRED_CONTRACTS = [
  { name: "OtpSender", file: "app/Contracts/OtpSender.php", methods: ["send"] },
  { name: "EmailSender", file: "app/Contracts/EmailSender.php", methods: ["send"] },
  {
    name: "StorageDriver",
    file: "app/Contracts/StorageDriver.php",
    methods: ["put", "url", "delete"],
  },
  { name: "ErrorReporter", file: "app/Contracts/ErrorReporter.php", methods: ["report"] },
  { name: "SupportLinkProvider", file: "app/Contracts/SupportLinkProvider.php", methods: ["url"] },
  {
    name: "KnowledgeBaseLinkProvider",
    file: "app/Contracts/KnowledgeBaseLinkProvider.php",
    methods: ["url"],
  },
  {
    name: "StatusPageLinkProvider",
    file: "app/Contracts/StatusPageLinkProvider.php",
    methods: ["url"],
  },
  { name: "PrinterDriver", file: "app/Contracts/PrinterDriver.php", methods: ["print", "testPrint"] },
];

export const REQUIRED_ADAPTERS = [
  "app/Services/Adapters/Otp/LogOtpSender.php",
  "app/Services/Adapters/Otp/FakeOtpSender.php",
  "app/Services/Adapters/Otp/FonnteOtpSender.php",
  "app/Services/Adapters/Otp/EmailOtpSender.php",
  "app/Services/Adapters/Otp/CompositeOtpSender.php",
  "app/Services/Adapters/Email/LogEmailSender.php",
  "app/Services/Adapters/Email/FakeEmailSender.php",
  "app/Services/Adapters/Email/MailpitEmailSender.php",
  "app/Services/Adapters/Email/SesEmailSender.php",
  "app/Services/Adapters/Storage/LocalStorageDriver.php",
  "app/Services/Adapters/Storage/FakeStorageDriver.php",
  "app/Services/Adapters/Storage/S3StorageDriver.php",
  "app/Services/Adapters/Error/LogErrorReporter.php",
  "app/Services/Adapters/Error/FakeErrorReporter.php",
  "app/Services/Adapters/Error/SentryErrorReporter.php",
  "app/Services/Adapters/Link/MailtoLinkProvider.php",
  "app/Services/Adapters/Link/ChatwootLinkProvider.php",
  "app/Services/Adapters/Link/StaticMarkdownLinkProvider.php",
  "app/Services/Adapters/Link/BookStackLinkProvider.php",
  "app/Services/Adapters/Link/InstatusLinkProvider.php",
  "app/Services/Adapters/Link/UptimeKumaLinkProvider.php",
  "app/Services/Adapters/Printer/LogPrinterDriver.php",
  "app/Services/Adapters/Printer/FakePrinterDriver.php",
  "app/Services/Adapters/Printer/BluetoothPrinterDriver.php",
  "app/Services/Adapters/Printer/CssPrintDriver.php",
];

export const REQUIRED_MIGRATIONS = [
  { table: "plans", file: "database/migrations/2026_11_28_000001_create_plans_table.php" },
  { table: "features", file: "database/migrations/2026_11_28_000002_create_features_table.php" },
  {
    table: "plan_features",
    file: "database/migrations/2026_11_28_000003_create_plan_features_table.php",
  },
  { table: "add_ons", file: "database/migrations/2026_11_28_000004_create_add_ons_table.php" },
  {
    table: "admin_users",
    file: "database/migrations/2026_11_28_000005_create_admin_users_table.php",
  },
  {
    table: "admin_sessions",
    file: "database/migrations/2026_11_28_000006_create_admin_sessions_table.php",
  },
  { table: "partners", file: "database/migrations/2026_11_28_000007_create_partners_table.php" },
];

export const REQUIRED_SEEDERS = [
  "database/seeders/PlanSeeder.php",
  "database/seeders/FeatureSeeder.php",
  "database/seeders/PlanFeatureSeeder.php",
  "database/seeders/AddOnSeeder.php",
  "database/seeders/AdminSeeder.php",
  "database/seeders/DatabaseSeeder.php",
];

export const GUARD_PRESETS = {
  local: {
    label: "Local Docker Dev (.env.example)",
    otp_driver: "log",
    mail_mailer: "log",
    app_debug: true,
    telescope_enabled: false,
    session_driver: "redis",
    queue_connection: "redis",
    error_driver: "log",
    storage_driver: "local",
    app_url: "http://localhost:8000",
    force_https: false,
    fonnte_token: "",
    fonnte_url: "",
    sentry_dsn: "",
    aws_access_key_id: "",
    aws_bucket: "",
  },
  staging: {
    label: "Staging (Render + TiDB + Upstash + R2)",
    otp_driver: "fake",
    mail_mailer: "log",
    app_debug: true,
    telescope_enabled: false,
    session_driver: "redis",
    queue_connection: "redis",
    error_driver: "sentry",
    storage_driver: "r2",
    app_url: "https://kasirku-staging.onrender.com",
    force_https: true,
    fonnte_token: "",
    fonnte_url: "",
    sentry_dsn: "https://public@sentry.example/1",
    aws_access_key_id: "R2_KEY_SET",
    aws_bucket: "kasirku-staging",
  },
  "production-safe": {
    label: "Production Jakarta (Aman / RG Pass)",
    otp_driver: "composite",
    mail_mailer: "ses",
    app_debug: false,
    telescope_enabled: false,
    session_driver: "redis",
    queue_connection: "redis",
    error_driver: "glitchtip",
    storage_driver: "minio",
    app_url: "https://kasirku.id",
    force_https: true,
    fonnte_token: "CONFIGURED",
    fonnte_url: "https://api.fonnte.com/send",
    sentry_dsn: "https://glitchtip.kasirku.internal/1",
    aws_access_key_id: "MINIO_KEY_SET",
    aws_bucket: "kasirku-prod",
  },
};

export function evaluateProductionGuards(snapshot = GUARD_PRESETS.local) {
  const otpDriver = String(snapshot.otp_driver || "log")
    .trim()
    .toLowerCase();
  const mailMailer = String(snapshot.mail_mailer || "log")
    .trim()
    .toLowerCase();
  const appDebug = Boolean(snapshot.app_debug);
  const telescopeEnabled = Boolean(snapshot.telescope_enabled);
  const sessionDriver = String(snapshot.session_driver || "file")
    .trim()
    .toLowerCase();
  const queueConnection = String(snapshot.queue_connection || "sync")
    .trim()
    .toLowerCase();
  const errorDriver = String(snapshot.error_driver || "log")
    .trim()
    .toLowerCase();
  const storageDriver = String(snapshot.storage_driver || "local")
    .trim()
    .toLowerCase();
  const appUrl = String(snapshot.app_url || "").trim();
  const forceHttps = Boolean(snapshot.force_https);

  const missingCreds = [];
  if (["fonnte", "composite"].includes(otpDriver)) {
    if (!String(snapshot.fonnte_token || "").trim()) missingCreds.push("FONNTE_TOKEN");
    if (!String(snapshot.fonnte_url || "").trim()) missingCreds.push("FONNTE_URL");
  }
  if (["sentry", "glitchtip"].includes(errorDriver) && !String(snapshot.sentry_dsn || "").trim()) {
    missingCreds.push("SENTRY_DSN");
  }
  if (["s3", "minio", "r2"].includes(storageDriver)) {
    if (!String(snapshot.aws_access_key_id || "").trim()) missingCreds.push("AWS_ACCESS_KEY_ID");
    if (!String(snapshot.aws_bucket || "").trim()) missingCreds.push("AWS_BUCKET");
  }

  const rules = [
    {
      rule: 1,
      envKey: "OTP_DRIVER",
      passed: !["", "log", "fake"].includes(otpDriver),
      actual: otpDriver || "(empty)",
      expected: "fonnte | email | composite",
    },
    {
      rule: 2,
      envKey: "MAIL_MAILER",
      passed: !["", "log", "array"].includes(mailMailer),
      actual: mailMailer || "(empty)",
      expected: "ses | postmark | smtp",
    },
    {
      rule: 3,
      envKey: "APP_DEBUG",
      passed: appDebug === false,
      actual: String(appDebug),
      expected: "false",
    },
    {
      rule: 4,
      envKey: "TELESCOPE_ENABLED",
      passed: telescopeEnabled === false,
      actual: String(telescopeEnabled),
      expected: "false",
    },
    {
      rule: 5,
      envKey: "SESSION_DRIVER",
      passed: sessionDriver === "redis",
      actual: sessionDriver || "(empty)",
      expected: "redis",
    },
    {
      rule: 6,
      envKey: "QUEUE_CONNECTION",
      passed: !["", "sync"].includes(queueConnection),
      actual: queueConnection || "(empty)",
      expected: "redis",
    },
    {
      rule: 7,
      envKey: "PROVIDER_CREDENTIALS",
      passed: missingCreds.length === 0,
      actual: missingCreds.length === 0 ? "configured" : `missing:${missingCreds.join(",")}`,
      expected: "Non-empty active provider keys",
    },
    {
      rule: 8,
      envKey: "APP_URL & FORCE_HTTPS",
      passed: appUrl.toLowerCase().startsWith("https://") && forceHttps === true,
      actual: `${appUrl || "(empty)"} | FORCE_HTTPS=${String(forceHttps)}`,
      expected: "https://* & FORCE_HTTPS=true",
    },
  ];

  const failedCount = rules.filter((r) => !r.passed).length;
  return {
    passed: failedCount === 0,
    failedCount,
    totalRules: rules.length,
    rules,
  };
}

export function extractPlanFeatureMatrix() {
  const featureSource = fs.readFileSync(
    path.join(rootDir, "database", "seeders", "FeatureSeeder.php"),
    "utf8"
  );
  const planSource = fs.readFileSync(
    path.join(rootDir, "database", "seeders", "PlanSeeder.php"),
    "utf8"
  );
  const matrixSource = fs.readFileSync(
    path.join(rootDir, "database", "seeders", "PlanFeatureSeeder.php"),
    "utf8"
  );

  const featureCodes = [...featureSource.matchAll(/'code'\s*=>\s*'([^']+)'/g)].map((m) => m[1]);
  const planCodes = [...planSource.matchAll(/'code'\s*=>\s*'([^']+)'/g)].map((m) => m[1]);

  const extractGroup = (varName) => {
    const regex = new RegExp(`\\$${varName}\\s*=\\s*\\[([\\s\\S]*?)\\];`);
    const match = matrixSource.match(regex);
    if (!match) return [];
    return [...match[1].matchAll(/'([^']+)'/g)].map((m) => m[1]);
  };

  const basic = extractGroup("basicFeatures");
  const starter = [...basic, ...extractGroup("starterFeatures")];
  const advance = [...starter, ...extractGroup("advanceFeatures")];
  const prime = [...advance, ...extractGroup("primeFeatures")];

  const planMap = {
    trial: new Set(featureCodes),
    starter_basic: new Set(basic),
    starter: new Set(starter),
    advance: new Set(advance),
    prime: new Set(prime),
    prime_plus: new Set(featureCodes),
  };

  const rows = featureCodes.map((code) => ({
    code,
    plans: Object.fromEntries(planCodes.map((p) => [p, Boolean(planMap[p]?.has(code))])),
  }));

  return {
    planCodes,
    featureCodes,
    totalMappings: planCodes.length * featureCodes.length,
    rows,
  };
}

export function collectSubFase0BStatus() {
  const contracts = REQUIRED_CONTRACTS.map((item) => {
    const fullPath = path.join(rootDir, item.file);
    const exists = fs.existsSync(fullPath);
    const src = exists ? fs.readFileSync(fullPath, "utf8") : "";
    const methodsFound = item.methods.every((method) => src.includes(`function ${method}(`));
    return { ...item, exists, methodsFound };
  });

  const adapters = REQUIRED_ADAPTERS.map((relPath) => ({
    path: relPath,
    exists: fs.existsSync(path.join(rootDir, relPath)),
  }));

  const migrations = REQUIRED_MIGRATIONS.map((item) => {
    const fullPath = path.join(rootDir, item.file);
    const exists = fs.existsSync(fullPath);
    const src = exists ? fs.readFileSync(fullPath, "utf8") : "";
    return {
      ...item,
      exists,
      hasUtf8mb4: src.includes("utf8mb4_unicode_ci"),
      hasDown: src.includes("public function down(): void") && src.includes("Schema::dropIfExists"),
    };
  });

  const seeders = REQUIRED_SEEDERS.map((relPath) => ({
    path: relPath,
    exists: fs.existsSync(path.join(rootDir, relPath)),
  }));

  return {
    contracts,
    adapters,
    migrations,
    seeders,
    matrix: extractPlanFeatureMatrix(),
  };
}
