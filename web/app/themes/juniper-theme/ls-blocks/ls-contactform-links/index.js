(()=>{"use strict";var e,t,n,a,r={83558:(e,t,n)=>{n.d(t,{Z:()=>s});var a=n(69307),r=(n(99196),n(52175)),l=n(4981),o=n(65736);const s=e=>{let{attributes:t,setAttributes:n}=e;const s=(0,l.getBlockDefaultClassName)("ls/contactform-links");return(0,a.createElement)("div",(0,r.useBlockProps)(),(0,a.createElement)("div",{className:`${s}__link-text`},(0,a.createElement)("div",{className:`${s}__inner`},(0,a.createElement)(r.RichText,{tagName:"span",onChange:e=>n({linkTextContact:e}),value:t.linkTextContact,placeholder:(0,o.__)("Text","iwgplating")}))),(0,a.createElement)("div",{className:`${s}__link-text`},(0,a.createElement)("div",{className:`${s}__inner`},(0,a.createElement)(r.RichText,{tagName:"span",onChange:e=>n({linkTextProductfinder:e}),value:t.linkTextProductfinder,placeholder:(0,o.__)("Text","iwgplating")}))))}},59343:(e,t,n)=>{n.d(t,{Z:()=>s});var a=n(69307),r=(n(99196),n(4981)),l=n(52175);const{lang:o}=wpVars,s=e=>{let{attributes:{linkTextContact:t,linkTextProductfinder:n}}=e;const o=(0,r.getBlockDefaultClassName)("ls/contactform-links");let s;return s="de-AT"===document.documentElement.lang||"de-DE"===document.documentElement.lang?"/productfinder":(document.documentElement.lang,"/en/productfinder"),(0,a.createElement)("div",l.useBlockProps.save(),(0,a.createElement)("div",{className:`${o}__link-text`},(0,a.createElement)("div",{className:`${o}__inner active`},(0,a.createElement)(l.RichText.Content,{tagName:"span",className:"icon-contact",value:t}))),(0,a.createElement)("div",{className:`${o}__link-text`},(0,a.createElement)("a",{href:s,className:`${o}__inner`},(0,a.createElement)(l.RichText.Content,{tagName:"span",className:"icon-muster",value:n}))))}},99196:e=>{e.exports=window.React},52175:e=>{e.exports=window.wp.blockEditor},4981:e=>{e.exports=window.wp.blocks},69307:e=>{e.exports=window.wp.element},65736:e=>{e.exports=window.wp.i18n},2119:e=>{e.exports=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"ls/contactform-links","version":"0.1.0","title":"LS Contact Form Links","category":"ls","icon":"admin-page","description":"LS Contact Form Links","supports":{"html":false},"textdomain":"iwgplating","attributes":{"link":{"type":"string","default":""},"linkTextContact":{"type":"string","default":""},"linkTextProductfinder":{"type":"string","default":""}}}')}},l={};function o(e){var t=l[e];if(void 0!==t)return t.exports;var n=l[e]={exports:{}};return r[e](n,n.exports,o),n.exports}o.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return o.d(t,{a:t}),t},o.d=(e,t)=>{for(var n in t)o.o(t,n)&&!o.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),e=o(4981),t=o(59343),n=o(83558),a=o(2119),(0,e.registerBlockType)(a,{edit:n.Z,save:t.Z})})();