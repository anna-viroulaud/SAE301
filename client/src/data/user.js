import { postRequest, getRequest } from "../lib/api-request.js";

let UserData = {};

// envoi en FormData car postRequest doit rester tel quel
UserData.signup = async function({ username, email, password }) {
  const fd = new FormData();
  fd.append("username", username);
  fd.append("email", email);
  fd.append("password", password);
  return await postRequest("users", fd);
};

UserData.login = async function({ email, password }) {
  const fd = new FormData();
  fd.append("email", email);
  fd.append("password", password);
  return await postRequest("users/login", fd);
};

UserData.logout = async function() {
  const fd = new FormData();
  return await postRequest("users/logout", fd);
};

UserData.getProfile = async function() {
  return await getRequest("users/profile");
};

UserData.updateProfile = async function({ username, email, password = null }) {
  const fd = new FormData();
  fd.append("username", username);
  fd.append("email", email);
  if (password) fd.append("password", password);
  return await postRequest("users/profile", fd); // backend doit gérer POST /api/users/profile
};

UserData.getOrders = async function() {
  return await getRequest("orders"); // backend orders à implémenter
};

export { UserData };