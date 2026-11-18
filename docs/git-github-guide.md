# GIT & GITHUB GUIDE — KASIRKU

Panduan setup Git lokal, pembuatan repository **private** di GitHub, strategi branch (`develop` & `main`), serta aturan commit sesuai **Rule Kasirku v2.0 §5 & §7**.

---

## 1. Branch Strategy (Rule §5)

| Branch | Fungsi | Aturan |
|---|---|---|
| `main` | Staging-ready / Production baseline | **Protected** — tidak boleh push langsung |
| `develop` | Integrasi utama tim MVP | Wajib jadi base branch fitur/fix/chore |
| `feat/*` | Fitur baru (`feat/<scope>-<deskripsi>`) | Dari `develop`, merge via PR |
| `fix/*` | Bug fix (`fix/<scope>-<deskripsi>`) | Dari `develop`, merge via PR |
| `chore/*` | Task non-fitur (`chore/<deskripsi>`) | Dari `develop`, merge via PR |
| `hotfix/*` | Perbaikan darurat | Dari `main` + post-merge review 24 jam |
| `staging` | Auto-deploy staging (Fase 7) | Dari `main`, merge via PR |
| `release/*` | Persiapan rilis production (Fase 9) | Dari `main` |

---

## 2. Inisialisasi & Identitas Git Lokal

Atur nama dan email Git kamu di laptop lokal:

```bash
git config user.name "Nama Kamu"
git config user.email "email@kamu.com"
```

Jika folder hasil export belum memiliki direktori `.git`, jalankan inisialisasi awal:

```bash
chmod +x scripts/init-git.sh
./scripts/init-git.sh
```

Atau secara manual:

```bash
git init -b develop
git add .
git commit -m "chore(foundation): setup Laravel 13 + Docker + project structure"
git branch -f main develop
git checkout develop
```

Pastikan saat ini berada di branch `develop`:

```bash
git branch
# * develop
#   main
```

---

## 3. Membuat Repository Private di GitHub & Push

> **Aturan Keamanan (Rule §2 & Step 0.1):** Repository GitHub **WAJIB Private** dan tidak boleh mengandung file `.env` atau secret apa pun.

### Opsi A — Menggunakan GitHub CLI (`gh`)

1. Login ke GitHub CLI (jika belum):
   ```bash
   gh auth login
   ```
2. Buat repo private dan set remote `origin`:
   ```bash
   gh repo create kasirku --private --source=. --remote=origin
   ```
3. Push branch `develop` dan `main`:
   ```bash
   git push -u origin develop
   git push -u origin main
   ```
4. Pastikan kembali berada di `develop`:
   ```bash
   git checkout develop
   ```

### Opsi B — Manual via Browser GitHub

1. Buka https://github.com/new
2. Isi **Repository name**: `kasirku`
3. Pilih **Private** (WAJIB)
4. **JANGAN** centang *Add a README file*, *Add .gitignore*, atau *Choose a license*
5. Klik **Create repository**
6. Salin URL SSH/HTTPS repo, lalu jalankan di terminal:
   ```bash
   git remote add origin git@github.com:USERNAME/kasirku.git
   git push -u origin develop
   git push -u origin main
   git checkout develop
   ```

---

## 4. Proteksi Branch `main` di GitHub (Direkomendasikan)

1. Masuk ke GitHub repo → **Settings** → **Branches** → **Add branch protection rule**
2. **Branch name pattern**: `main`
3. Centang:
   - **Require a pull request before merging** (minimal 1 approval; 2 untuk 18 kategori PR kritis)
   - **Require status checks to pass before merging** (CI 11 langkah setelah dikonfigurasi)
   - **Do not allow bypassing the above settings**

---

## 5. Verifikasi di GitHub

Buka `https://github.com/USERNAME/kasirku` dan pastikan:
- [ ] Visibility repo = **Private**
- [ ] Branch `develop` ada di remote
- [ ] Branch `main` ada di remote
- [ ] File `.env` **TIDAK** ikut ter-push (hanya `.env.example`)
- [ ] Folder `node_modules/` dan `vendor/` **TIDAK** ikut ter-push

---

## 6. Commit Convention (Rule §7)

Format wajib:

```text
<type>(<scope>): <deskripsi singkat>

[optional body]

[optional footer: Refs: Fase X Step X.Y]
```

- **Type:** `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`
- **Subject:** maksimal 72 karakter
- **Larangan commit:** secret, `.env`, `node_modules/`, `vendor/`, `console.log`/`dd()`/`dump()`, kode mati, `innerHTML` untuk data user, import React/Vue/TS/Tailwind.
