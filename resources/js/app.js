// Kasirku — Main Entry Point
// Akan diisi lengkap di Step 0.4
const appVersion =
  document.querySelector('meta[name="app-version"]')?.getAttribute('content') ||
  '0.1.0';
const appEnv =
  document.querySelector('meta[name="app-env"]')?.getAttribute('content') ||
  'local';

export function getAppInfo() {
  return { version: appVersion, env: appEnv };
}

document.addEventListener('DOMContentLoaded', () => {
  const versionBadge = document.getElementById('kasirku-version-label');
  if (versionBadge) {
    versionBadge.textContent = `v${appVersion} (${appEnv})`;
  }
});
