import template from "./template.html?raw";
import { htmlToFragment, genericRenderer } from "../../lib/utils";

export async function HomePage() {
    const fragment = htmlToFragment(template);
    
    // Vérifier si l'utilisateur est connecté
    const userDataString = sessionStorage.getItem('user');
    const user = userDataString ? JSON.parse(userDataString) : null;
    
    // Afficher le message personnalisé si connecté
    const welcomeTitle = fragment.querySelector('#welcomeTitle');
    const welcomeMessage = fragment.querySelector('#welcomeMessage');
    
    if (user && user.username) {
        welcomeTitle.textContent = `Bonjour, ${user.username} ! 🎉`;
        welcomeMessage.innerHTML = `
            <p class="text-lg mb-4">
                Nous sommes ravis de vous revoir ! Découvrez nos nouveautés et profitez d'une expérience shopping unique.
            </p>
            <div class="flex gap-4 mt-6">
                <a href="/products" data-link class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    Voir les produits
                </a>
                <a href="/profile" data-link class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                    Mon espace personnel
                </a>
            </div>
        `;
    } else {
        welcomeTitle.textContent = 'Bienvenue sur Søstrene Grene';
        welcomeMessage.innerHTML = `
            <p class="text-lg mb-4">
                Découvrez une sélection variée de produits et profitez d'une expérience d'achat simple et rapide avec notre service Click & Collect.
            </p>
            <div class="flex gap-4 mt-6">
                <a href="/products" data-link class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    Découvrir nos produits
                </a>
                <a href="/login" data-link class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Se connecter
                </a>
            </div>
        `;
    }
    
    return fragment;
}
