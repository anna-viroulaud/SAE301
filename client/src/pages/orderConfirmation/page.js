import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";
import { getRequest } from "../../lib/api-request.js";

let C = {};

C.init = async function(params) {
    const orderId = params.id;
    
    if (!orderId) {
        return V.createErrorFragment("Commande introuvable");
    }
    
    // Vérifier l'authentification
    const user = sessionStorage.getItem('user');
    if (!user) {
        window.router?.navigate('/login');
        return htmlToFragment('<div>Redirection...</div>');
    }
    
    try {
        // Récupérer les détails de la commande
        const order = await getRequest(`carts/${orderId}`);
        
        if (!order || order.error) {
            return V.createErrorFragment(order?.error || "Commande introuvable");
        }
        
        return V.init(order);
    } catch (error) {
        console.error("Erreur lors du chargement de la commande:", error);
        return V.createErrorFragment("Erreur lors du chargement");
    }
}

let V = {};

V.init = function(order) {
    const fragment = htmlToFragment(template);
    
    // Remplir les données de la commande
    fragment.querySelector('#orderNumber').textContent = order.orderNumber || 'N/A';
    fragment.querySelector('#orderTotal').textContent = V.formatPrice(order.totalAmount || 0);
    fragment.querySelector('#orderDate').textContent = V.formatDate(order.dateCommande);
    fragment.querySelector('#orderItems').innerHTML = V.generateOrderItems(order.items || []);
    
    V.attachEvents(fragment);
    
    return fragment;
}

V.createErrorFragment = function(message) {
    return htmlToFragment(`
        <main class="max-w-2xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Erreur</h1>
                <p class="text-gray-600 mb-6">${message}</p>
                <a href="/cart" data-link class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300 transition">
                    Retour au panier
                </a>
            </div>
        </main>
    `);
}

V.generateOrderItems = function(items) {
    if (!items || items.length === 0) {
        return '<p class="text-gray-500">Aucun article</p>';
    }
    
    return items.map(item => `
        <div class="flex justify-between items-center py-3 border-b">
            <div class="flex-1">
                <div class="font-medium text-gray-900">${item.productName || 'Produit'}</div>
                <div class="text-sm text-gray-600">Quantité: ${item.quantity} × ${V.formatPrice(item.unitPrice)}</div>
            </div>
            <div class="font-medium text-gray-900">${V.formatPrice(item.totalPrice)}</div>
        </div>
    `).join('');
}

V.formatPrice = function(price) {
    const num = parseFloat(price) || 0;
    return num.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
}

V.formatDate = function(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

V.attachEvents = function(fragment) {
    // Gérer les liens de navigation
    fragment.querySelectorAll('a[data-link]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const href = link.getAttribute('href');
            if (href && window.router) {
                window.router.navigate(href);
            }
        });
    });
}

export function OrderConfirmationPage(params) {
    return C.init(params);
}
