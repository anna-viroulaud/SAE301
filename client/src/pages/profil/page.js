import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";
import { UserData } from "../../data/user.js";

export function ProfilePage() {
  const frag = htmlToFragment(template);
  const infoDiv = frag.querySelector("#profileInfo");
  const editBtn = frag.querySelector("#editBtn");
  const logoutBtn = frag.querySelector("#logoutBtn");
  const editForm = frag.querySelector("#profileEditForm");
  const msg = frag.querySelector("#profileMsg");
  const cancelBtn = frag.querySelector("#cancelEdit");
  const recentOrdersRoot = frag.querySelector("#recentOrders");

  function escapeHtml(s){ return String(s || "").replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  function renderInfo(user) {
    if (!user) { infoDiv.textContent = "Non connecté."; return; }
    infoDiv.innerHTML = `
      <div class="mb-1"><b>Nom d'utilisateur :</b> ${escapeHtml(user.username)}</div>
      <div><b>Email :</b> ${escapeHtml(user.email)}</div>
    `;
  }

  function renderRecentOrders(orders) {
    if (!orders || !orders.length) {
      recentOrdersRoot.innerHTML = `<div class="text-sm text-stone-600">Aucune commande récente.</div>`;
      return;
    }
    recentOrdersRoot.innerHTML = orders.slice(0,5).map(o => {
      const date = o.date || o.created_at || "";
      const total = o.total != null ? `${o.total}€` : "";
      const status = o.status ? ` — ${escapeHtml(o.status)}` : "";
      return `<div class="py-1 border-b text-sm">#${escapeHtml(String(o.id))} ${date} — ${total}${status}</div>`;
    }).join("");
  }

  // initial render from sessionStorage then refresh from server
  (async () => {
    let cached = null;
    try { cached = JSON.parse(sessionStorage.getItem("user") || "null"); } catch(e){ cached = null; }
    renderInfo(cached);

    try {
      const profile = await UserData.getProfile();
      if (profile && !profile.error) {
        sessionStorage.setItem("user", JSON.stringify(profile));
        renderInfo(profile);
        if (window.router && typeof window.router.setAuth === "function") window.router.setAuth(true);
      }
    } catch (e) { /* ignore */ }

    // fetch recent orders
    try {
      const orders = await UserData.getOrders();
      renderRecentOrders(orders && !orders.error ? orders : []);
    } catch (e) {
      recentOrdersRoot.innerHTML = `<div class="text-sm text-stone-600">Impossible de charger les commandes.</div>`;
    }
  })();

  // Edit open
  editBtn.addEventListener("click", (e) => {
    e.preventDefault();
    msg.textContent = "";
    const cached = JSON.parse(sessionStorage.getItem("user") || "null") || {};
    editForm.username.value = cached.username || "";
    editForm.email.value = cached.email || "";
    editForm.password.value = "";
    editForm.password_confirm.value = "";
    editForm.style.display = "";
    editBtn.style.display = "none";
  });

  // Cancel
  cancelBtn.addEventListener("click", () => {
    editForm.style.display = "none";
    editBtn.style.display = "";
    msg.textContent = "";
  });

  // Submit update
  editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    msg.style.color = "red";
    msg.textContent = "";

    const username = editForm.username.value.trim();
    const email = editForm.email.value.trim();
    const password = editForm.password.value;
    const password_confirm = editForm.password_confirm.value;

    if (!username || !email) { msg.textContent = "Nom d'utilisateur et email requis."; return; }
    // simple email format check
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { msg.textContent = "Email invalide."; return; }
    if (password) {
      if (password.length < 8) { msg.textContent = "Le mot de passe doit contenir au moins 8 caractères."; return; }
      if (password !== password_confirm) { msg.textContent = "Les mots de passe ne correspondent pas."; return; }
    }

    try {
      const res = await UserData.updateProfile({ username, email, password: password || null });
      if (res && !res.error) {
        sessionStorage.setItem("user", JSON.stringify(res));
        renderInfo(res);
        msg.style.color = "green";
        msg.textContent = "Profil mis à jour.";
        editForm.style.display = "none";
        editBtn.style.display = "";
        editForm.password.value = "";
        editForm.password_confirm.value = "";
        // update nav label if present
        const navLabel = document.querySelector("#navUserLabel");
        if (navLabel) navLabel.textContent = res.username || res.email || "Mon compte";
        if (window.router && typeof window.router.setAuth === "function") window.router.setAuth(true);
      } else {
        msg.textContent = res?.message || "Erreur lors de la mise à jour.";
      }
    } catch (err) {
      msg.textContent = "Erreur réseau.";
    }
  });

  // Logout
  logoutBtn.addEventListener("click", async () => {
    try {
      await UserData.logout();
    } catch (_) {}
    sessionStorage.removeItem("user");
    if (window.router && typeof window.router.setAuth === "function") {
      window.router.setAuth(false);
      window.router.navigate("/login");
    } else {
      window.location.href = "/login";
    }
  });

  return frag;
}