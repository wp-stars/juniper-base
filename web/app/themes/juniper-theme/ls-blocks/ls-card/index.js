(()=>{"use strict";var e,t,n,a,l={81150:(e,t,n)=>{n.d(t,{Z:()=>r});var a=n(69307),l=n(70444);const r=(0,a.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,a.createElement)(l.Path,{d:"M4 19.8h8.9v-1.5H4v1.5zm8.9-15.6H4v1.5h8.9V4.2zm-8.9 7v1.5h16v-1.5H4z"}))},74734:(e,t,n)=>{n.d(t,{Z:()=>r});var a=n(69307),l=n(70444);const r=(0,a.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,a.createElement)(l.Path,{fillRule:"evenodd",d:"M9.706 8.646a.25.25 0 01-.188.137l-4.626.672a.25.25 0 00-.139.427l3.348 3.262a.25.25 0 01.072.222l-.79 4.607a.25.25 0 00.362.264l4.138-2.176a.25.25 0 01.233 0l4.137 2.175a.25.25 0 00.363-.263l-.79-4.607a.25.25 0 01.072-.222l3.347-3.262a.25.25 0 00-.139-.427l-4.626-.672a.25.25 0 01-.188-.137l-2.069-4.192a.25.25 0 00-.448 0L9.706 8.646zM12 7.39l-.948 1.921a1.75 1.75 0 01-1.317.957l-2.12.308 1.534 1.495c.412.402.6.982.503 1.55l-.362 2.11 1.896-.997a1.75 1.75 0 011.629 0l1.895.997-.362-2.11a1.75 1.75 0 01.504-1.55l1.533-1.495-2.12-.308a1.75 1.75 0 01-1.317-.957L12 7.39z",clipRule:"evenodd"}))},81138:(e,t,n)=>{n.d(t,{Z:()=>r});var a=n(69307),l=n(70444);const r=(0,a.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,a.createElement)(l.Path,{d:"M5 4v11h14V4H5zm3 15.8h8v-1.5H8v1.5z"}))},38714:(e,t,n)=>{n.d(t,{Z:()=>d});var a=n(69307),l=(n(99196),n(52175)),r=n(55609),i=n(4981),c=n(81150),o=n(81138),s=n(74734);const d=e=>{let{attributes:t,setAttributes:n}=e;const{contentType:d,linkText:m,link:p}=t,g=(0,i.getBlockDefaultClassName)("ls/card"),h=["core/heading","core/paragraph","core/image"],u=[{icon:c.Z,title:"Card with Image, Heading and Description",isActive:"card-image-heading-description"===d,onClick:()=>n({contentType:"card-image-heading-description"})},{icon:o.Z,title:"Card with Image and Heading",isActive:"card-image-heading"===d,onClick:()=>n({contentType:"card-image-heading"})},{icon:s.Z,title:"Card with Link",isActive:"card-link"===d,onClick:()=>n({contentType:"card-link"})}];return(0,a.createElement)("div",(0,l.useBlockProps)(),(0,a.createElement)(l.InspectorControls,null,(0,a.createElement)(r.Panel,{header:"Card Block Panel"},(0,a.createElement)(r.PanelBody,{title:"Card Block Settings",initialOpen:!0,className:"ls-card-settings-panel"},(0,a.createElement)(r.PanelRow,{title:"Set Link Text"},(0,a.createElement)("div",null,(0,a.createElement)("p",null,"Set Link Text"),(0,a.createElement)(l.RichText,{onChange:e=>n({linkText:e}),value:t.linkText})),(0,a.createElement)("div",null,(0,a.createElement)("p",null,"Enter URL (including https:// or http://"),(0,a.createElement)(l.RichText,{onChange:e=>n({link:e}),value:t.link})))))),(0,a.createElement)(l.BlockControls,null,(0,a.createElement)(r.ToolbarGroup,{controls:u})),(0,a.createElement)("div",null,"card-image-heading-description"===d&&(0,a.createElement)(l.InnerBlocks,{className:g+"__card-image-heading-description",allowedBlocks:h,template:[["core/image",{sizeSlug:"thumbnail"}],["core/heading",{content:"Title...",level:3}],["core/paragraph",{content:"Description..."}]],templateLock:"all"}),"card-link"===d&&(0,a.createElement)(l.InnerBlocks,{className:g+"card-link",allowedBlocks:h,template:[["core/image",{sizeSlug:"thumbnail"}],["core/image",{sizeSlug:"thumbnail"}],["core/heading",{content:"Title...",level:3}],["core/heading",{content:"Subtitle...",level:5}],["core/paragraph",{content:"Description..."}]],templateLock:"all"}),"card-image-heading"===d&&(0,a.createElement)(l.InnerBlocks,{className:g+"card-image-heading",allowedBlocks:h,template:[["core/image",{sizeSlug:"medium"}],["core/heading",{content:"Title...",level:3}]],templateLock:"all"})))}},50033:(e,t,n)=>{n.d(t,{Z:()=>c});var a=n(87462),l=n(69307),r=(n(99196),n(52175)),i=n(4981);const c=e=>{let{attributes:t}=e;const{contentType:n,linkText:c,link:o}=t,s=(0,i.getBlockDefaultClassName)("ls/card"),d=(0,l.createElement)("div",(0,a.Z)({},r.useBlockProps.save(),{className:s+"__"+n}),(0,l.createElement)(r.InnerBlocks.Content,null),"card-link"===n&&0!==o.length&&(0,l.createElement)("div",{className:"link-text"},(0,l.createElement)("span",null,c)," ",(0,l.createElement)("i",{className:"icon-arrow"})));return(0,l.createElement)("div",{className:s},0!==o.length&&(0,l.createElement)("a",{href:o},d),0===o.length&&(0,l.createElement)("a",null,d))}},99196:e=>{e.exports=window.React},52175:e=>{e.exports=window.wp.blockEditor},4981:e=>{e.exports=window.wp.blocks},55609:e=>{e.exports=window.wp.components},69307:e=>{e.exports=window.wp.element},70444:e=>{e.exports=window.wp.primitives},87462:(e,t,n)=>{function a(){return a=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var a in n)Object.prototype.hasOwnProperty.call(n,a)&&(e[a]=n[a])}return e},a.apply(this,arguments)}n.d(t,{Z:()=>a})},61605:e=>{e.exports=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"ls/card","version":"0.1.0","title":"LS Card Block","category":"ls","icon":"admin-page","description":"LS Card Block","supports":{"html":false},"textdomain":"iwgplating","attributes":{"contentType":{"type":"string","default":"card-image-heading-description"},"link":{"type":"string","default":""},"linkText":{"type":"string","default":"Produkte entdecken"}}}')}},r={};function i(e){var t=r[e];if(void 0!==t)return t.exports;var n=r[e]={exports:{}};return l[e](n,n.exports,i),n.exports}i.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return i.d(t,{a:t}),t},i.d=(e,t)=>{for(var n in t)i.o(t,n)&&!i.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),e=i(4981),t=i(50033),n=i(38714),a=i(61605),(0,e.registerBlockType)(a,{edit:n.Z,save:t.Z})})();