import { getRequest } from '../lib/api-request.js';

let ProductImageData = {};

ProductImageData.fetchByProductId = async function (id) {
  const data = await getRequest(`productimages?product_id=${id}`);
  return Array.isArray(data) ? data : [];
};

ProductImageData.fetchAll = async function () {
  const data = await getRequest('productimages');
  return Array.isArray(data) ? data : [];
};

export { ProductImageData };