import {getRequest} from '../lib/api-request.js';


let CategoryData = {};

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

CategoryData.fetchByCategory = async function(categoryId) {
    let data = await getRequest('products?category=' + categoryId);
    return data == false ? fakeProducts : data;
}


export {CategoryData};