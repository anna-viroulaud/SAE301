/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,html}",
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Brandon Grotesque', 'system-ui', 'sans-serif'],
        'brandon': ['Brandon Grotesque', 'system-ui', 'sans-serif'],
        'gibson': ['Gibson', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
