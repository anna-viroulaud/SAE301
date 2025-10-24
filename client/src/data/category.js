import {getRequest} from '../lib/api-request.js';


let CategoryData = {};

let fakeCategories = [
    {
        id: 1,
        name: "Mobilier",
        productCount: 7
    },
    {
        id: 2,
        name: "Électronique",
        productCount: 6
    },
    {
        id: 3,
        name: "Bureautique",
        productCount: 5
    },
    {
        id: 4,
        name: "Cuisine",
        productCount: 6
    },
    {
        id: 5,
        name: "Extérieur",
        productCount: 6
    }
];

let fakeProducts = [
    {
        id: 1,
        name: "Marteau",
    },
    {
        id: 2,
        name: "Tournevis",
    },
    {
        id: 3,
        name: "Clé à molette",
    },
    
]

/**
 * Récupérer tous les produits d'une catégorie
 */
CategoryData.fetchByCategory = async function(categoryId) {
    let data = await getRequest('products?category=' + categoryId);
    return data == false ? fakeProducts : data;
}

/**
 * Récupérer toutes les catégories
 */
CategoryData.fetchAll = async function() {
    let data = await getRequest('categories');
    return data == false ? fakeCategories : data;
}

/**
 * Récupérer une catégorie par son ID
 */
CategoryData.fetch = async function(categoryId) {
    let data = await getRequest('categories/' + categoryId);
    if (data == false) {
        // Fallback sur les fake data
        return fakeCategories.find(c => c.id == categoryId) || null;
    }
    return data;
}


export {CategoryData};