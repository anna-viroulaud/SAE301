/**
 *  Besoin de comprendre comment fonctionne fetch ?
 *  C'est ici : https://fr.javascript.info/fetch
 */


let API_URL = "http://mmi.unilim.fr/~viroulaud8/api/";


/**
 *  getRequest
 * 
 *  Requête en GET l'URI uri. 
 *  Une requête en GET correspond à une demande de "lecture" de la ressource d'URI uri.
 * 
 *  Par exemple "http://.../products" pour lire tous les produits
 *  Ou "http://.../products/3" pour lire le produit d'identifiant 3
 * 
 *  Le serveur renverra les données au format JSON.
 *  La fonction les retourne après conversion en objet Javascript (ou false si la requête a échoué)
 * 
 *  ATTENTION : La fonction est asynchrone, donc quand on l'appelle il ne faut pas oublier "await".
 *  Exemple : let data = await getRequest(http://.../api/products);
 */
let getRequest = async function(uri){
    let options = {
        method: "GET",
        credentials: "include",             // <--- envoyer cookies de session
        headers: { "Accept": "application/json" }
    };

    try{
        var response = await fetch(API_URL+uri, options);
    }
    catch(e){
        console.error("Echec de la requête : "+e);
        return false;
    }
    if (response.status != 200){
        console.error("Erreur de requête : " + response.status);
        return false;
    }
    let $obj = await response.json();
    return $obj;
}


/**
 *  postRequest
 * 
 *  Requête en POST l'URI uri. Par exemple "http://.../products"
 * 
 *  Une requête en POST correspond à une demande de création d'une ressource (dans l'exemple, création d'un produit)
 *  Pour créer la ressource, on fournit les données utiles via le paramètre data.
 * 
 *  Le serveur retourne en JSON la nouvelle ressource créée en base avec son identifiant.
 *  La fonction retourne les données après conversion en objet Javascript (ou false si la requête a échoué)
 */

let postRequest = async function(uri, data,){
    // Défition des options de la requêtes
    let options = {
        credentials: 'include', // inclure les cookies dans la requête
        method: 'POST',
        header: {
            Content_Type: 'multipart/form-data' // type de données envoyées (nécessaire si upload fichier)
        },
        body: data
    }

    try {
    var response = await fetch(API_URL + uri, options);
  } catch (e) {
    console.error("Échec de la requête : " + e);
    return false;
  }

  let raw = await response.text(); // <-- récupère le texte brut
  console.log("Réponse brute du serveur :", raw); // <-- affiche dans la console

  try {
    return JSON.parse(raw); // essaie de parser
  } catch (e) {
    console.error("Erreur de parsing JSON :", e);
    return false;
  }
}


let jsonpostRequest = async function(uri, data){
    let options = {
        method: 'POST',
        credentials: 'include',              // <--- envoyer cookies de session
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    };

    try {
        var response = await fetch(API_URL + uri, options);
    } catch (e) {
        console.error("Échec de la requête : " + e);
        throw new Error("Échec de la connexion au serveur");
    }

    let raw = await response.text();
    console.log("Réponse brute du serveur :", raw);

    if (!raw || raw.trim() === '') {
        throw new Error("Le serveur n'a pas renvoyé de réponse");
    }

    try {
        return JSON.parse(raw);
    } catch (e) {
        console.error("Erreur de parsing JSON :", e);
        throw new Error("Réponse du serveur invalide: " + raw.substring(0, 100));
    }
}




/**
 *  deleteRequest
 * 
 *  Requête en DELETE l'URI uri. Par exemple "http://.../products/3"
 * 
 *  Une requête en DELETE correspond à une demande de suppression d'une ressource.
 *  Par exemple : patchRequest("http://.../products/3") pour supprimer le produit d'identifiant 3
 * 
 *  La fonction retourne true ou false selon le succès de l'opération
 */
let deleteRequest = async function(uri){
   // Pas implémenté. TODO if needed.
}


/** 
 *  patchRequest
 * 
 *  Requête en PATCH l'URI uri. Par exemple "http://.../products/3"
 * 
 *  Une requête en PATCH correspond à une demande de modification/mise à jour d'une ressource.
 *  Pour modifier la ressource, on fournit les données utiles via le paramètre data.
 *  Par exemple : patchRequest("http://.../products/3", {category:1} ) pour modifier la catégorie du produit d'identifiant 3
 * 
 *  La fonction retourne true ou false selon le succès de l'opération
 */
let patchRequest = async function(uri, data, opts = {}) {
  const headers = opts.headers || {};
  let body;
  if (data instanceof FormData) {
    body = data;
  } else if (opts.json) {
    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
    body = JSON.stringify(data);
  } else {
    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
    body = typeof data === 'string' ? data : JSON.stringify(data);
  }
  try {
    var response = await fetch(API_URL + uri, { method: 'PATCH', credentials: 'include', headers, body });
  } catch (e) {
    console.error("Échec de la requête :", e);
    return false;
  }
  const raw = await response.text();
  try { return JSON.parse(raw); } catch (e) { console.error("Parsing JSON :", e); return false; }
}



export {getRequest, postRequest, jsonpostRequest, patchRequest}