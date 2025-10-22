import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";

export function ProfilePage() {
  let frag = htmlToFragment(template);
  let info = frag.querySelector("#profileInfo");
  let user = JSON.parse(sessionStorage.getItem("user") || "null");

  if (user) {
    info.innerHTML = `
      <div><b>Nom d'utilisateur :</b> ${user.username}</div>
      <div><b>Email :</b> ${user.email}</div>
    `;
  } else {
    info.textContent = "Non connecté.";
  }

  frag.querySelector("#logoutBtn").addEventListener("click", async () => {
    await postRequest("users/logout", {});
    sessionStorage.removeItem("user");
    window.router.setAuth(false);
    window.router.navigate("/");
  });

  return frag;
}