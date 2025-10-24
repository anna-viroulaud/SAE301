import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";
import { UserData } from "../../data/user.js";

export function ProfilePage() {
  const frag = htmlToFragment(template);
  const form = frag.querySelector("#profileEditForm");
  const editBtn = frag.querySelector("#editBtn");
  const logoutBtn = frag.querySelector("#logoutBtn");
  const modifyBtn = frag.querySelector("#modifyBtn");
  const cancelBtn = frag.querySelector("#cancelEdit");
  const msg = frag.querySelector("#profileMsg");
  const recentOrders = frag.querySelector("#recentOrders");

  const names = ["username","email","firstName","lastName","dob","password","password_confirm"];
  const get = n => form.querySelector(`[name="${n}"]`);

  const setEditing = on => {
    names.forEach(n => { const el = get(n); if (el) el.disabled = !on; });
    modifyBtn.style.display = on ? "" : "none";
    cancelBtn.style.display = on ? "" : "none";
    editBtn.disabled = on;
  };

  const fill = user => {
    if (!user) return;
    get("username") && (get("username").value = user.username ?? "");
    get("email") && (get("email").value = user.email ?? "");
    // show username in firstName if firstName missing
    get("firstName") && (get("firstName").value = user.firstName ?? user.username ?? "");
    get("lastName") && (get("lastName").value = user.lastName ?? "");
    get("dob") && (get("dob").value = user.dob ?? "");
  };

  (async () => {
    const res = await UserData.getProfile();
    if (!res || res.error) { sessionStorage.removeItem("user"); window.router?.setAuth(false); window.router?.navigate("/login"); return; }
    const user = res.user ?? res;
    sessionStorage.setItem("user", JSON.stringify(user));
    fill(user);
    setEditing(false);
    recentOrders.textContent = "Aucune commande récente.";
  })();

  editBtn.addEventListener("click", () => {
    setEditing(true);
    get("email")?.focus();
  });

  cancelBtn.addEventListener("click", () => {
    const stored = JSON.parse(sessionStorage.getItem("user") || "null") || {};
    fill(stored);
    msg.textContent = "";
    setEditing(false);
  });

  form.addEventListener("submit", async e => {
    e.preventDefault();
    msg.textContent = "";
    const payload = {};
    const email = (get("email")?.value || "").trim();
    if (!email) { msg.textContent = "Email requis."; return; }
    payload.email = email;
    const username = (get("username")?.value || "").trim();
    if (username) payload.username = username;
    const firstName = (get("firstName")?.value || "").trim();
    if (firstName) payload.firstName = firstName;
    const lastName = (get("lastName")?.value || "").trim();
    if (lastName) payload.lastName = lastName;
    const dob = (get("dob")?.value || "").trim();
    if (dob) payload.dob = dob;
    const password = get("password")?.value || "";
    const confirm = get("password_confirm")?.value || "";
    if (password) {
      if (password.length < 8) { msg.textContent = "Mot de passe trop court (min 8)."; return; }
      if (password !== confirm) { msg.textContent = "Les mots de passe ne correspondent pas."; return; }
      payload.password = password;
    }

    const res = await UserData.updateProfile(payload);
    if (res && !res.error) {
      const user = res.user ?? res;
      sessionStorage.setItem("user", JSON.stringify(user));
      fill(user);
      setEditing(false);
      msg.style.color = "green";
      msg.textContent = "Profil mis à jour.";
      setTimeout(() => { msg.textContent = ""; msg.style.color = ""; }, 2500);
    } else {
      msg.textContent = res?.message || res?.error || "Erreur lors de la mise à jour.";
    }
  });

  logoutBtn.addEventListener("click", async () => {
    await UserData.logout();
    sessionStorage.removeItem("user");
    window.router?.setAuth && window.router.setAuth(false);
    window.router?.navigate("/");
  });

  return frag;
}