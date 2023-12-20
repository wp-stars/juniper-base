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
  
    },
    extend: {

    },
  },
  variants: {
    display:['group-hover']
  },
  plugins: [
    require('flowbite/plugin'),
    require('@tailwindcss/line-clamp')
  ]
}

