import { postRequest, getRequest, patchRequest } from "../lib/api-request.js";

let UserData = {};

// signup -> POST /api/users (garde si ton back gère /users pour création)
UserData.signup = async function({ firstName, lastName, email, dateOfBirth, password }) {
  const fd = new FormData();
  fd.append("firstName", firstName);
  fd.append("lastName", lastName);
  fd.append("email", email);
  fd.append("dateOfBirth", dateOfBirth);
  fd.append("password", password);
  return await postRequest("users", fd);
};

// login -> POST /api/auth/login (corrigé)
UserData.login = async function({ email, password }) {
  const fd = new FormData();
  fd.append("email", email);
  fd.append("password", password);
  return await postRequest("auth/login", fd);
};

// logout -> POST /api/auth/logout (ou auth/logout selon ton controller)
UserData.logout = async function() {
  const fd = new FormData();
  return await postRequest("auth/logout", fd);
};

UserData.getProfile = async function() {
  return await getRequest("users/profile");
};

UserData.updateProfile = async function(payload) {
  return await patchRequest("users/profile", payload, { json: true });
};

export { UserData };