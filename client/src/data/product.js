import {getRequest} from '../lib/api-request.js';


let ProductData = {};

let fakeProducts = [
    {
        id: 1,
        name: "Marteau",
        description: "Un marteau est un outil utilisé pour enfoncer des clous dans du bois ou d'autres matériaux. Il se compose d'une tête lourde en métal fixée à un manche en bois ou en fibre de verre.",
        price: 9.99,
    },
    {
        id: 2,
        name: "Tournevis",
        description: "Un tournevis est un outil utilisé pour visser ou dévisser des vis. Il se compose d'une tige en métal avec une tête qui s'adapte à la fente de la vis.",
        price: 5.99
    },
    {
        id: 3,
        name: "Clé à molette",
        description: "Une clé à molette est un outil utilisé pour serrer ou desserrer des écrous et des boulons. Elle se compose d'une mâchoire réglable qui s'adapte à différentes tailles d'écrous.",
        price: 12.99
    },
    {
        id: 4,
        name: "Pince",
        description: "Une pince est un outil utilisé pour saisir, tenir ou plier des objets. Elle se compose de deux bras articulés qui se rejoignent en un point de pivot.",
        price: 7.99
    },
    {
        id: 5,
        name: "Scie",
        description: "Une scie est un outil utilisé pour couper des matériaux, généralement en bois. Elle se compose d'une lame dentée fixée à un manche.",
        price: 14.99
    },
    {
        id: 6,
        name: "Perceuse",
        description: "Une perceuse est un outil utilisé pour percer des trous dans divers matériaux. Elle se compose d'un moteur qui fait tourner une mèche.",
        price: 49.99
    },
    {
        id: 7,
        name: "Ponceuse",
        description: "Une ponceuse est un outil utilisé pour lisser des surfaces en bois ou en métal. Elle se compose d'un moteur qui fait vibrer ou tourner un abrasif.",
        price: 79.99
    },
    {
        id: 8,
        name: "Mètre",
        description: "Un mètre est un outil utilisé pour mesurer des distances. Il se compose d'une bande graduée en métal ou en plastique.",
        price: 19.99
    },
    {
        id: 9,
        name: "Niveau à bulle",
        description: "Un niveau à bulle est un outil utilisé pour vérifier l'horizontalité ou la verticalité d'une surface. Il se compose d'un tube rempli de liquide avec une bulle d'air à l'intérieur.",
        price: 9.99
    }
]

ProductData.fetch = async function (id) {
  let data = await getRequest("products/" + id);
  return data == false ? fakeProducts.pop() : [data];
};

ProductData.fetchAll = async function () {
  let data = await getRequest("products");
  return data == false ? fakeProducts : data;
};


export {ProductData};