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
    container: {
      center: true,
      screens: {
        sm: '540px',
        md: '728px',
        lg: '984px',
        xl: '1000px',
        '2xl': '1200px',
      },
      padding: {
        DEFAULT: '1.25rem',
        sm: '0'
      }
    },
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
  variants: {
    display:['group-hover']
  },
  plugins: [
    require('flowbite/plugin')
  ]
}

