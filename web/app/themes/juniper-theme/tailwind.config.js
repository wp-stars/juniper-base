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

        //light mode
        bg: '#ffffff',
        font_color: '#1a1a1a',
        element_border_color: '#c2c2c2',
        primary_color: '#482bd9',
        only_txt_btn: '#482bd9',
        primary_btn_bg: '#482bd9',
        primary_btn_txt: '#f6f4ff',
        secondary_btn_txt: '#482bd9',
        secondary_btn_border: '#482bd9',
        font_color_reverse: '#ffffff',
        bg_reverse: '#1a1a1a',

        //dark mode
        bg_dark: '#1a1a1a',
        font_color_dark: '##ffffff',
        element_border_color_dark: '#424242',
        primary_color_dark: '#d8d0ff',
        only_txt_btn_dark: '#d8d0ff',
        primary_btn_bg_dark: '#f6f4ff',
        primary_btn_txt_dark: '#482bd9',
        secondary_btn_txt_dark: '#d8d0ff',
        secondary_btn_border_dark: '#d8d0ff',
        font_color_reverse_dark: '#1a1a1a',
        bg_reverse_dark: '#ffffff',

      },
      customClasses:{
        'btn': 'w-20 h-10 px-4 py-2 rounded justify-center items-center gap-1 inline-flex',
        'btn-primary': 'bg-primary_color text-primary_btn_txt'

      }
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

