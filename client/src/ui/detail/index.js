import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

const IMG_BASE = '/'; // ajuste si besoin (ex: '/~viroulaud8/images/')

function resolveUrl(u) {
  if (!u) return '';
  if (/^https?:\/\//i.test(u) || u.startsWith('/')) return u;
  return IMG_BASE + u;
}

let DetailView = {
  html: (data) => genericRenderer(template, data),

  dom(data) {
    const fragment = htmlToFragment(this.html(data));
    const gallery = fragment.querySelector('[data-gallery]');
    if (!gallery) return fragment;

    const imgs = Array.isArray(data?.images) ? data.images : (data?.image ? [data.image] : []);
    const urls = imgs
      .map(x => typeof x === 'string' ? x : x?.url)
      .filter(Boolean)
      .map(resolveUrl);

    gallery.innerHTML = urls.map((u, i) => `
      <img
        src="${u}"
        alt="${(data?.name || 'image')} ${i + 1}"
        class="w-full h-full object-contain"
        loading="lazy"
      />
    `).join('');

    return fragment;
  }
};

export { DetailView };