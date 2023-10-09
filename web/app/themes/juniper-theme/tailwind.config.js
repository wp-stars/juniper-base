/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    "./**/*.twig",
    "./**/*.php",
    "./**/*.html",
    "./**/*.js",
    "!**/node_modules/**",
    "!**/vendor/**",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#B4D43D',
        dark: '#093642',
        light: '#F9F9F9',
        accent: '#B4D43D',
        black: '#1e1e1e',
        white: '#F9F9F9'
      },
    },
  },
  plugins: [
    require('flowbite/plugin')
  ]
}

