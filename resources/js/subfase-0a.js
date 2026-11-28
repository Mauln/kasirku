const state = {
  activeTab: "overview",
  changeFilter: "ALL",
  manifest: null,
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
  "6. Test JS Vanilla — Node test runner (tests/Unit/SubFase0A/*.test.mjs)",
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
    cmd: "# Cek struktur sebelum memindahkan agar tidak menimpa vendor/ atau artisan:\n# Hapus starter React/TS/Tailwind (src/, tsconfig.json, vite.config.ts)\n# Pertahankan konfigurasi Docker, CI, ESLint, Stylelint, Husky, dan docs/",
    desc: "File template React/TS/Tailwind bawaan AI Studio telah digantikan oleh struktur Vanilla Frontend + Docker + CI sesuai Aturan Emas #41-42.",
  },
  {
    title: "4. Setup Git, Branch Strategy & GitHub",
    status: "Pre-commit Aktif",
    cmd: 'git init -b main\ngit checkout -b develop\ngit add .\ngit commit -m "chore(init): inisialisasi repo, docker lokal, dan pipeline ci"\ngit remote add origin <URL_REPO_GITHUB_ANDA>\ngit push -u origin main develop',
    desc: "Branch main dilindungi (dilarang commit langsung oleh .husky/pre-commit). Gunakan Conventional Commits yang divalidasi oleh .husky/commit-msg.",
  },
  {
    title: "5. Jalankan Stack Docker Lokal (7 Services)",
    status: "Siap Dijalankan",
    cmd: "docker compose up -d --build\ndocker compose ps\ndocker compose exec app php artisan about\n# Akses http://localhost:8000 dan phpMyAdmin http://localhost:8080",
    desc: "Menjalankan app, nginx, mysql, redis, queue, scheduler, dan phpmyadmin sekaligus secara konsisten.",
  },
];

function createStatusPill(label, variant) {
  return el("span", `status-pill status-pill--${variant}`, label);
}

function renderHeroMetrics() {
  const container = document.getElementById("hero-metrics");
  if (!container) return;
  const counts = state.manifest?.summary || {
    new: 0,
    modified: 0,
    deleted: 0,
    moved: 0,
  };
  const metrics = [
    { value: "7", label: "Service Docker Lokal" },
    { value: "11", label: "Langkah CI GitHub Actions" },
    { value: "0", label: "Framework Terlarang (Vanilla)" },
    {
      value: `${counts.new + counts.modified + counts.deleted + counts.moved}`,
      label: "Perubahan File Terdokumentasi",
    },
  ];

  const nodes = metrics.map((m) => {
    const box = el("div", "metric-box");
    box.append(el("span", "metric-box__value", m.value));
    box.append(el("span", "metric-box__label", m.label));
    return box;
  });
  container.replaceChildren(...nodes);
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
  cardGit.append(el("h2", "card__title", "Git, Git Hooks & Kesiapan Sub-Fase 0A"));
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
    ["Docs Panduan Laravel, 0A & Perubahan Struktur", "LENGKAP", "ok"],
    ["Docker & Composer runtime di container AI Studio", "LOKAL USER", "warn"],
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
  card.append(el("h2", "card__title", "Dokumentasi Perubahan Struktur Project (Sub-Fase 0A)"));
  card.append(
    el("p", "card__desc", "Sumber data langsung dari docs/sub-fase-0a/MANIFEST_PERUBAHAN.json.")
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

  const entries = (state.manifest?.entries || []).filter(
    (item) => state.changeFilter === "ALL" || item.changeType === state.changeFilter
  );

  const tableWrap = el("div", "table-wrap");
  const table = el("table", "data-table");
  const thead = el("thead");
  const headRow = el("tr");
  for (const heading of ["Status", "Path File / Folder", "Fungsi & Alasan"]) {
    headRow.append(el("th", "", heading));
  }
  thead.append(headRow);
  table.append(thead);

  const tbody = el("tbody");
  if (entries.length === 0) {
    const emptyRow = el("tr");
    const emptyCell = el("td", "", "Tidak ada item pada kategori ini.");
    emptyCell.colSpan = 3;
    emptyRow.append(emptyCell);
    tbody.append(emptyRow);
  } else {
    for (const item of entries) {
      const row = el("tr");
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

  const panels = ["overview", "laravel", "infra", "changes"];
  for (const name of panels) {
    const panel = document.getElementById(`panel-${name}`);
    if (panel) {
      panel.hidden = name !== state.activeTab;
    }
  }
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
    }
  });
}

async function init() {
  bindEvents();
  try {
    const response = await fetch("/api/subfase-0a/manifest");
    if (response.ok) {
      state.manifest = await response.json();
    }
  } catch (err) {
    console.warn("Gagal memuat manifest perubahan:", err);
  }

  renderHeroMetrics();
  renderOverviewPanel();
  renderLaravelPanel();
  renderInfraPanel();
  renderChangesPanel();
  renderActiveTab();
}

init();
