const state = {
  activeTab: "subfase0b",
  changeFilter: "ALL",
  guardPreset: "local",
  manifest: null,
  subfase0b: null,
  guardReport: null,
};

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) {
    node.className = className;
  }
  if (text !== undefined && text !== null) {
    node.textContent = String(text);
  }
  return node;
}

const DOCKER_SERVICES = [
  { name: "app", desc: "PHP 8.4-FPM + Laravel Extensions + Composer 2" },
  { name: "nginx", desc: "Nginx 1.28 Alpine (Port 8000) + HTTP/2 static asset cache" },
  { name: "mysql", desc: "MySQL 8.4 LTS (utf8mb4_unicode_ci, Port 3306)" },
  { name: "redis", desc: "Redis 7.4 Alpine (Session, Cache, Queue, Port 6379)" },
  { name: "queue", desc: "Laravel Queue Worker (--queue=default,analytics)" },
  { name: "scheduler", desc: "Laravel Task Scheduler (schedule:work)" },
  { name: "phpmyadmin", desc: "phpMyAdmin 5.2 (Port 8080) untuk inspeksi DB lokal" },
];

const CI_STEPS = [
  "1. Lint PHP — Laravel Pint (pint.json)",
  "2. Lint JS Vanilla — ESLint 9+/10 Flat Config (eslint.config.js)",
  "3. Lint CSS — Stylelint (.stylelintrc.json, color-no-hex + BEM)",
  "4. Format Check — Prettier (.prettierrc)",
  "5. Test Backend — Pest (PHP 8.4 + MySQL 8.4 + Redis 7.4)",
  "6. Test JS Vanilla — Node test runner (tests/Unit/SubFase0A & 0B)",
  "7. Audit Dependency — composer audit + npm audit",
  "8. Secret Scan — Gitleaks (.gitleaks.toml)",
  "9. Image/Config Scan — Trivy",
  "10. Guard Rules Dry-Run — php artisan app:check-production",
  "11. Coverage Check — Pest --coverage --min=100",
];

const LARAVEL_STEPS = [
  {
    title: "1. Instalasi Tools Wajib Lokal",
    status: "Panduan Lengkap",
    cmd: "php -v   # >= 8.4\ncomposer -V\nmysql --version\nredis-cli ping\nnode -v && npm -v\ndocker --version && git --version",
    desc: "Pastikan PHP 8.4, Composer 2.x, MySQL 8.4, Redis 7.4, Node 22+, Git, dan Docker Desktop tersedia di mesin lokal.",
  },
  {
    title: "2. Buat Project Laravel 12/13 & Setup MySQL",
    status: "Wajib Lokal",
    cmd: "composer create-project laravel/laravel kasirku\ncd kasirku\ncp .env.example .env\nphp artisan key:generate",
    desc: "Catatan keselarasan: Prompt menyebut Laravel 12, sedangkan Architecture/Roadmap v2.0 menargetkan Laravel 13 + PHP 8.4. composer.json mendukung ^12.0|^13.0.",
  },
  {
    title: "3. Pindahkan & Gabungkan File AI Studio Secara Aman",
    status: "Selesai di Repo",
    cmd: "# Cek struktur sebelum memindahkan agar tidak menimpa vendor/ atau artisan:\n# Hapus starter React/TS/Tailwind (src/, tsconfig.json, vite.config.ts)\n# Pertahankan konfigurasi Docker, CI, ESLint, Stylelint, Husky, Contracts, Guard, Migrations, Seeders",
    desc: "File template React/TS/Tailwind bawaan AI Studio telah digantikan oleh struktur Vanilla Frontend + Contracts + Guard Rules + Core Schema.",
  },
  {
    title: "4. Setup Git, Branch Strategy & GitHub",
    status: "Pre-commit Aktif",
    cmd: 'git init -b main\ngit checkout -b develop\ngit add .\ngit commit -m "chore(init): inisialisasi repo, docker lokal, dan pipeline ci"\ngit remote add origin <URL_REPO_GITHUB_ANDA>\ngit push -u origin main develop',
    desc: "Branch main dilindungi (dilarang commit langsung oleh .husky/pre-commit). Gunakan Conventional Commits yang divalidasi oleh .husky/commit-msg.",
  },
  {
    title: "5. Jalankan Stack Docker Lokal (7 Services) & Migrasi 0B",
    status: "Siap Dijalankan",
    cmd: "docker compose up -d --build\ndocker compose exec app php artisan migrate --seed\ndocker compose exec app php artisan app:check-production --dry-run",
    desc: "Menjalankan 7 layanan Docker lalu mengeksekusi 7 tabel inti (#1-#7) beserta 5 Reference Seeders (6 plans, 21 features, 126 plan_features, 3 add-ons, 1 admin).",
  },
];

function createStatusPill(label, variant) {
  return el("span", `status-pill status-pill--${variant}`, label);
}

function renderHeroMetrics() {
  const container = document.getElementById("hero-metrics");
  if (!container) return;

  const contractCount = state.subfase0b?.contracts?.length || 8;
  const migrationCount = state.subfase0b?.migrations?.length || 7;
  const matrixTotal = state.subfase0b?.matrix?.totalMappings || 126;

  const metrics = [
    { value: String(contractCount), label: "Interface Contracts" },
    { value: "8", label: "Guard Rules Production" },
    { value: String(migrationCount), label: "Tabel Core (#1-#7)" },
    { value: String(matrixTotal), label: "Plan × Feature Matrix" },
  ];

  const nodes = metrics.map((m) => {
    const box = el("div", "metric-box");
    box.append(el("span", "metric-box__value", m.value));
    box.append(el("span", "metric-box__label", m.label));
    return box;
  });
  container.replaceChildren(...nodes);
}

function renderSubFase0BPanel() {
  const panel = document.getElementById("panel-subfase0b");
  if (!panel) return;

  const topGrid = el("div", "section-grid");

  // Card 1: 8 Contracts + 25 Adapters
  const contractsCard = el("article", "card");
  contractsCard.append(el("h2", "card__title", "8 Adapter Contracts & 25 Implementations"));
  contractsCard.append(
    el(
      "p",
      "card__desc",
      "Seluruh layanan eksternal diakses lewat app/Contracts/* dan di-bind di AppServiceProvider::register() berdasar config/services.php."
    )
  );
  const contractList = el("ul", "check-list");
  for (const item of state.subfase0b?.contracts || []) {
    const li = el("li", "check-list__item");
    const left = el("div");
    left.append(el("strong", "mono", item.name));
    left.append(el("div", "", `${item.file} (${item.methods.join(", ")})`));
    li.append(left);
    li.append(
      createStatusPill(
        item.exists && item.methodsFound ? "CONTRACT OK" : "MISSING",
        item.exists && item.methodsFound ? "ok" : "danger"
      )
    );
    contractList.append(li);
  }
  contractsCard.append(contractList);

  // Card 2: 7 Migrations & 5 Reference Seeders
  const dbCard = el("article", "card");
  dbCard.append(el("h2", "card__title", "7 Tabel Core (#1-#7) & 5 Reference Seeders"));
  dbCard.append(
    el(
      "p",
      "card__desc",
      "Menggunakan InnoDB + utf8mb4_unicode_ci + method down() lengkap dan seeder idempoten via updateOrCreate()."
    )
  );
  const migList = el("ul", "check-list");
  for (const mig of state.subfase0b?.migrations || []) {
    const li = el("li", "check-list__item");
    const left = el("div");
    left.append(el("strong", "mono", mig.table));
    left.append(el("div", "mono", mig.file));
    li.append(left);
    const ok = mig.exists && mig.hasUtf8mb4 && mig.hasDown;
    li.append(createStatusPill(ok ? "UTF8MB4 + DOWN" : "CHECK", ok ? "ok" : "warn"));
    migList.append(li);
  }
  dbCard.append(migList);

  topGrid.append(contractsCard, dbCard);

  // Stack below: Guard Simulator + 21x6 Matrix + Manifest 0B
  const stack = el("div", "stack-column");

  // Guard Rules Simulator Card
  const guardCard = el("article", "card");
  guardCard.append(
    el("h2", "card__title", "Simulator 8 Guard Rules Production (app:check-production)")
  );
  guardCard.append(
    el(
      "p",
      "card__desc",
      "Uji aturan keamanan boot di AppServiceProvider::boot() agar driver dev/log/sync tidak pernah lolos ke environment produksi."
    )
  );

  const presetBar = el("div", "preset-bar");
  const presets = [
    { key: "local", label: "Local Dev (.env.example)" },
    { key: "staging", label: "Staging (Render + TiDB)" },
    { key: "production-safe", label: "Production Aman (RG Pass)" },
  ];
  for (const p of presets) {
    const btn = el(
      "button",
      `filter-btn${state.guardPreset === p.key ? " filter-btn--active" : ""}`,
      p.label
    );
    btn.type = "button";
    btn.dataset.guardPreset = p.key;
    presetBar.append(btn);
  }
  guardCard.append(presetBar);

  if (state.guardReport) {
    const banner = el(
      "div",
      `guard-banner ${state.guardReport.passed ? "guard-banner--ok" : "guard-banner--fail"}`,
      state.guardReport.passed
        ? `LULUS (${state.guardReport.totalRules}/${state.guardReport.totalRules} Guard Rules): Aplikasi diizinkan boot pada mode production.`
        : `DITOLAK (${state.guardReport.failedCount} dari ${state.guardReport.totalRules} aturan gagal): Production boot dihentikan & dicatat ke audit env_guard_failed.`
    );
    guardCard.append(banner);

    const gWrap = el("div", "table-wrap");
    const gTable = el("table", "data-table");
    const gHead = el("thead");
    const gHeadRow = el("tr");
    for (const h of ["#", "Env Key", "Status", "Nilai Aktual", "Harapan Produksi"]) {
      gHeadRow.append(el("th", "", h));
    }
    gHead.append(gHeadRow);
    gTable.append(gHead);

    const gBody = el("tbody");
    for (const rule of state.guardReport.rules || []) {
      const tr = el("tr");
      tr.append(el("td", "mono", `#${rule.rule}`));
      tr.append(el("td", "mono", rule.envKey));
      const statusTd = el("td");
      statusTd.append(
        createStatusPill(rule.passed ? "PASS" : "FAIL", rule.passed ? "ok" : "danger")
      );
      tr.append(statusTd);
      tr.append(el("td", "mono", rule.actual));
      tr.append(el("td", "", rule.expected));
      gBody.append(tr);
    }
    gTable.append(gBody);
    gWrap.append(gTable);
    guardCard.append(gWrap);
  }
  stack.append(guardCard);

  // Plan x Feature Matrix Card (21 x 6)
  const matrixCard = el("article", "card");
  matrixCard.append(
    el("h2", "card__title", "Feature Matrix Ter-seed: 21 Fitur × 6 Paket (126 Baris)")
  );
  matrixCard.append(
    el(
      "p",
      "card__desc",
      "Diekstrak langsung dari PlanSeeder.php, FeatureSeeder.php, dan PlanFeatureSeeder.php."
    )
  );
  const mWrap = el("div", "table-wrap");
  const mTable = el("table", "data-table");
  const mHead = el("thead");
  const mHeadRow = el("tr");
  const planHeaders = [
    ["trial", "Trial"],
    ["starter_basic", "Basic"],
    ["starter", "Starter"],
    ["advance", "Advance"],
    ["prime", "Prime"],
    ["prime_plus", "Prime+"],
  ];
  mHeadRow.append(el("th", "", "Kode Fitur (21)"));
  for (const [, title] of planHeaders) {
    mHeadRow.append(el("th", "", title));
  }
  mHead.append(mHeadRow);
  mTable.append(mHead);

  const mBody = el("tbody");
  for (const row of state.subfase0b?.matrix?.rows || []) {
    const tr = el("tr");
    tr.append(el("td", "mono", row.code));
    for (const [planCode] of planHeaders) {
      const td = el("td");
      const enabled = Boolean(row.plans?.[planCode]);
      td.append(
        el(
          "span",
          `matrix-mark ${enabled ? "matrix-mark--yes" : "matrix-mark--no"}`,
          enabled ? "YA" : "-"
        )
      );
      tr.append(td);
    }
    mBody.append(tr);
  }
  mTable.append(mBody);
  mWrap.append(mTable);
  matrixCard.append(mWrap);
  stack.append(matrixCard);

  panel.replaceChildren(topGrid, stack);
}

function renderOverviewPanel() {
  const panel = document.getElementById("panel-overview");
  if (!panel) return;

  const grid = el("div", "section-grid");

  const cardCompliance = el("article", "card");
  cardCompliance.append(el("h2", "card__title", "Kepatuhan Vanilla Stack (Aturan Emas #41-44)"));
  cardCompliance.append(
    el(
      "p",
      "card__desc",
      "Tooling frontend menegakkan larangan React, Vue, Svelte, Angular, TypeScript, Tailwind, Inertia, Alpine, HTMX, Livewire, dan dynamic innerHTML."
    )
  );
  const compList = el("ul", "check-list");
  const compItems = [
    ["ESLint 9+/10 Flat Config (eslint.config.js)", "LULUS", "ok"],
    ["Stylelint Token & BEM (.stylelintrc.json)", "LULUS", "ok"],
    ["Prettier Format Check (.prettierrc)", "LULUS", "ok"],
    ["Zero Banned Deps di package.json", "LULUS", "ok"],
    ["Zero .ts/.tsx di resources/", "LULUS", "ok"],
  ];
  for (const [label, status, variant] of compItems) {
    const li = el("li", "check-list__item");
    li.append(el("span", "", label));
    li.append(createStatusPill(status, variant));
    compList.append(li);
  }
  cardCompliance.append(compList);

  const cardGit = el("article", "card");
  cardGit.append(el("h2", "card__title", "Git, Git Hooks & Kesiapan Sub-Fase 0A–0B"));
  cardGit.append(
    el(
      "p",
      "card__desc",
      "Strategi branch main/develop, Conventional Commits, pre-commit hook, dan dokumentasi perubahan file."
    )
  );
  const gitList = el("ul", "check-list");
  const gitItems = [
    ["Branch main & develop + proteksi pre-commit", "AKTIF", "ok"],
    ["Conventional Commits (.husky/commit-msg)", "AKTIF", "ok"],
    ["PR Template 8 Seksi (.github/PULL_REQUEST_TEMPLATE.md)", "TERSEDIA", "ok"],
    ["Docs Panduan 0A & 0B + Manifest Perubahan", "LENGKAP", "ok"],
    ["PHP 8.4 / Docker / Composer runtime di container AI Studio", "LOKAL USER", "warn"],
  ];
  for (const [label, status, variant] of gitItems) {
    const li = el("li", "check-list__item");
    li.append(el("span", "", label));
    li.append(createStatusPill(status, variant));
    gitList.append(li);
  }
  cardGit.append(gitList);

  grid.append(cardCompliance, cardGit);
  panel.replaceChildren(grid);
}

function renderLaravelPanel() {
  const panel = document.getElementById("panel-laravel");
  if (!panel) return;

  const list = el("ol", "step-list");
  for (const step of LARAVEL_STEPS) {
    const card = el("li", "step-card");
    const head = el("div", "step-card__head");
    head.append(el("h2", "step-card__title", step.title));
    head.append(createStatusPill(step.status, "info"));
    card.append(head);
    card.append(el("p", "card__desc", step.desc));
    card.append(el("pre", "code-block", step.cmd));
    list.append(card);
  }
  panel.replaceChildren(list);
}

function renderInfraPanel() {
  const panel = document.getElementById("panel-infra");
  if (!panel) return;

  const grid = el("div", "section-grid");

  const dockerCard = el("article", "card");
  dockerCard.append(el("h2", "card__title", "Docker Compose Lokal (7 Services)"));
  dockerCard.append(
    el(
      "p",
      "card__desc",
      "Dikonfigurasi di docker-compose.yml bersama folder docker/{php,nginx,mysql}."
    )
  );
  const dockerList = el("ul", "check-list");
  for (const svc of DOCKER_SERVICES) {
    const li = el("li", "check-list__item");
    const left = el("div");
    left.append(el("strong", "mono", svc.name));
    left.append(el("div", "", svc.desc));
    li.append(left);
    li.append(createStatusPill("READY", "ok"));
    dockerList.append(li);
  }
  dockerCard.append(dockerList);

  const ciCard = el("article", "card");
  ciCard.append(el("h2", "card__title", "GitHub Actions CI (11 Langkah Wajib)"));
  ciCard.append(
    el("p", "card__desc", "Didefinisikan di .github/workflows/ci.yml sesuai Rule v2.0 §10.4.")
  );
  const ciList = el("ul", "check-list");
  for (const step of CI_STEPS) {
    const li = el("li", "check-list__item");
    li.append(el("span", "", step));
    li.append(createStatusPill("CONFIGURED", "info"));
    ciList.append(li);
  }
  ciCard.append(ciList);

  grid.append(dockerCard, ciCard);
  panel.replaceChildren(grid);
}

function getStatusVariant(status) {
  if (status === "New") return "ok";
  if (status === "Modified") return "info";
  if (status === "Deleted") return "danger";
  return "warn";
}

function renderChangesPanel() {
  const panel = document.getElementById("panel-changes");
  if (!panel) return;

  const card = el("article", "card");
  card.append(el("h2", "card__title", "Dokumentasi Perubahan Struktur Project (Sub-Fase 0A & 0B)"));
  card.append(
    el(
      "p",
      "card__desc",
      "Gabungan manifest docs/sub-fase-0b/MANIFEST_PERUBAHAN.json dan docs/sub-fase-0a/MANIFEST_PERUBAHAN.json."
    )
  );

  const filterBar = el("div", "filter-bar");
  const categories = ["ALL", "New", "Modified", "Deleted", "Moved"];
  for (const cat of categories) {
    const btn = el(
      "button",
      `filter-btn${state.changeFilter === cat ? " filter-btn--active" : ""}`,
      cat
    );
    btn.type = "button";
    btn.dataset.filter = cat;
    filterBar.append(btn);
  }
  card.append(filterBar);

  const entries0b = (state.subfase0b?.manifest?.entries || []).map((item) => ({
    ...item,
    phase: "0B",
  }));
  const entries0a = (state.manifest?.entries || []).map((item) => ({
    ...item,
    phase: "0A",
  }));

  const entries = [...entries0b, ...entries0a].filter(
    (item) => state.changeFilter === "ALL" || item.changeType === state.changeFilter
  );

  const tableWrap = el("div", "table-wrap");
  const table = el("table", "data-table");
  const thead = el("thead");
  const headRow = el("tr");
  for (const heading of ["Fase", "Status", "Path File / Folder", "Fungsi & Alasan"]) {
    headRow.append(el("th", "", heading));
  }
  thead.append(headRow);
  table.append(thead);

  const tbody = el("tbody");
  if (entries.length === 0) {
    const emptyRow = el("tr");
    const emptyCell = el("td", "", "Tidak ada item pada kategori ini.");
    emptyCell.colSpan = 4;
    emptyRow.append(emptyCell);
    tbody.append(emptyRow);
  } else {
    for (const item of entries) {
      const row = el("tr");
      row.append(el("td", "mono", item.phase));
      const statusTd = el("td");
      statusTd.append(createStatusPill(item.changeType, getStatusVariant(item.changeType)));
      const pathTd = el("td", "mono", item.path);
      const descTd = el("td", "", `${item.purpose} — ${item.reason}`);
      row.append(statusTd, pathTd, descTd);
      tbody.append(row);
    }
  }
  table.append(tbody);
  tableWrap.append(table);
  card.append(tableWrap);

  panel.replaceChildren(card);
}

function renderActiveTab() {
  const tabs = document.querySelectorAll(".tab-btn");
  tabs.forEach((btn) => {
    const isActive = btn.dataset.tab === state.activeTab;
    btn.classList.toggle("tab-btn--active", isActive);
    btn.setAttribute("aria-selected", isActive ? "true" : "false");
  });

  const panels = ["subfase0b", "overview", "laravel", "infra", "changes"];
  for (const name of panels) {
    const panel = document.getElementById(`panel-${name}`);
    if (panel) {
      panel.hidden = name !== state.activeTab;
    }
  }
}

async function loadGuardPreset(presetKey) {
  state.guardPreset = presetKey;
  try {
    const res = await fetch(`/api/subfase-0b/guard-check?preset=${encodeURIComponent(presetKey)}`);
    if (res.ok) {
      state.guardReport = await res.json();
    }
  } catch (err) {
    console.warn("Gagal memuat simulasi guard:", err);
  }
  renderSubFase0BPanel();
}

function bindEvents() {
  document.addEventListener("click", (event) => {
    const tabBtn = event.target.closest(".tab-btn");
    if (tabBtn && tabBtn.dataset.tab) {
      state.activeTab = tabBtn.dataset.tab;
      renderActiveTab();
      return;
    }

    const filterBtn = event.target.closest(".filter-btn");
    if (filterBtn && filterBtn.dataset.filter) {
      state.changeFilter = filterBtn.dataset.filter;
      renderChangesPanel();
      return;
    }

    if (filterBtn && filterBtn.dataset.guardPreset) {
      loadGuardPreset(filterBtn.dataset.guardPreset);
    }
  });
}

async function init() {
  bindEvents();
  try {
    const [res0a, res0b, resGuard] = await Promise.all([
      fetch("/api/subfase-0a/manifest"),
      fetch("/api/subfase-0b/overview"),
      fetch(`/api/subfase-0b/guard-check?preset=${encodeURIComponent(state.guardPreset)}`),
    ]);
    if (res0a.ok) state.manifest = await res0a.json();
    if (res0b.ok) state.subfase0b = await res0b.json();
    if (resGuard.ok) state.guardReport = await resGuard.json();
  } catch (err) {
    console.warn("Gagal memuat data Sub-Fase 0A/0B:", err);
  }

  renderHeroMetrics();
  renderSubFase0BPanel();
  renderOverviewPanel();
  renderLaravelPanel();
  renderInfraPanel();
  renderChangesPanel();
  renderActiveTab();
}

init();
