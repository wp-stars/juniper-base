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
    fontFamily: {
      body: ['Roboto']
    },
    colors: {
      lightgray: '#f3f0f0',
      black: '#000'
    },
    fontSize: {
      xs: '0.75rem',
      sm: '0.9rem',
      base: '1rem',
      xl: '1.25rem',
      '2xl': '1.563rem',
      '3xl': '1.953rem',
      '4xl': '2.441rem',
      '5xl': '3.052rem',
    },
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
    require('@tailwindcss/line-clamp')
  ]
}

