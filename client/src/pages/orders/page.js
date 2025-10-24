import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";
import { getRequest } from "../../lib/api-request.js";

// Fonction utilitaire pour formater le prix
function formatPrice(number) {
    return number.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Fonction pour formater la date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

// Fonction pour créer une carte de commande
function createOrderCard(order) {
    const orderDate = formatDate(order.date_commande || order.dateCommande);
    const orderTotal = formatPrice(parseFloat(order.total_amount || order.totalAmount || 0));
    const orderNumber = order.order_number || order.orderNumber || 'N/A';
    const items = order.items || [];
    
    // Statut avec couleur
    let statusClass = 'bg-green-100 text-green-800';
    let statusText = 'Validée';
    
    if (order.statut === 'En cours') {
        statusClass = 'bg-blue-100 text-blue-800';
        statusText = 'En cours';
    } else if (order.statut === 'Livrée') {
        statusClass = 'bg-green-100 text-green-800';
        statusText = 'Livrée';
    } else if (order.statut === 'Annulée') {
        statusClass = 'bg-red-100 text-red-800';
        statusText = 'Annulée';
    }
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition';
    
    let itemsHTML = '';
    items.forEach(item => {
        console.log('Item dans orders:', item); // DEBUG
        const itemName = item.productName || item.product_name || item.name || 'Produit';
        const itemQty = item.quantity || item.quantite || 1;
        // Chercher le prix dans tous les champs possibles
        const rawPrice = item.totalPrice || item.total_price || (item.unitPrice * itemQty) || item.price || item.prix || 0;
        const itemPrice = formatPrice(parseFloat(rawPrice));
        
        itemsHTML += `
            <div class="flex justify-between text-sm text-gray-600">
                <span>${itemName} x${itemQty}</span>
                <span>${itemPrice}€</span>
            </div>
        `;
    });
    
    card.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <div>
                <div class="text-sm text-gray-600">Commande</div>
                <div class="font-mono font-semibold text-lg">${orderNumber}</div>
            </div>
            <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium">
                ${statusText}
            </span>
        </div>
        
        <div class="text-sm text-gray-600 mb-4">
            Passée le ${orderDate}
        </div>
        
        <hr class="my-4">
        
        <div class="space-y-2 mb-4">
            ${itemsHTML}
        </div>
        
        <hr class="my-4">
        
        <div class="flex justify-between items-center">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="text-lg font-bold text-[#3f2f2a]">${orderTotal}€</span>
        </div>
        
        <div class="mt-4">
            <a href="/order-confirmation/${order.id}" data-link class="block w-full text-center bg-gray-100 text-gray-800 py-2 rounded hover:bg-gray-200 transition">
                Voir les détails
            </a>
        </div>
    `;
    
    return card;
}

export async function OrdersPage() {
    const frag = htmlToFragment(template);
    const ordersMsg = frag.querySelector('#ordersMsg');
    const ordersList = frag.querySelector('#ordersList');
    const noOrders = frag.querySelector('#noOrders');
    const loader = frag.querySelector('#ordersLoader');
    
    // Vérifier l'authentification
    const userStr = sessionStorage.getItem('user');
    if (!userStr) {
        window.router?.navigate('/login');
        return frag;
    }
    
    try {
        const user = JSON.parse(userStr);
        const userId = user.id;
        
        // Récupérer les commandes de l'utilisateur
        const response = await getRequest(`carts?userId=${userId}&orders=true`);
        
        console.log('Réponse API orders:', response); // DEBUG
        
        loader.style.display = 'none';
        
        if (!response || response.error) {
            ordersMsg.textContent = response?.error || 'Erreur lors du chargement des commandes.';
            noOrders.style.display = 'block';
            return frag;
        }
        
        // response peut être un tableau ou un objet avec une propriété orders
        const orders = Array.isArray(response) ? response : (response.orders || []);
        
        if (orders.length === 0) {
            noOrders.style.display = 'block';
        } else {
            // Trier par date décroissante
            orders.sort((a, b) => {
                const dateA = new Date(a.date_commande || a.dateCommande || 0);
                const dateB = new Date(b.date_commande || b.dateCommande || 0);
                return dateB - dateA;
            });
            
            // Créer les cartes de commandes
            orders.forEach(order => {
                const card = createOrderCard(order);
                ordersList.appendChild(card);
            });
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement des commandes:', error);
        loader.style.display = 'none';
        ordersMsg.textContent = 'Une erreur est survenue lors du chargement des commandes.';
        noOrders.style.display = 'block';
    }
    
    return frag;
}
