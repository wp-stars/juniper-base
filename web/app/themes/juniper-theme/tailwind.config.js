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
    colors: {
      primary: '#000',
      accent: '#FFEB00',
      lightgray: '#DCDDDE',
      black: '#000',
      gray: '#737373',
      darkgray: '#4D4D4D'
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
      fontFamily: {
        'sans': ['Roboto', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
      }
    },
  },
  variants: {
    display:['group-hover']
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
    function ({ addComponents }) {
      addComponents({
        '.btn': {
          display: 'flex',
          padding: '10px 24px',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '10px',
          borderRadius: '4px',
          backgroundColor: '#000',
          textWrap: 'nowrap'
        },
        '.btn-accent': {
          backgroundColor: '#FFEB00', // Use the accent color
        },
        '.btn-bordered': {
          border: '2px solid var(--color-black)',
          backgroundColor: 'transparent',
          color: 'var(--color-black)'
        },
      })
    }
  ]
}

