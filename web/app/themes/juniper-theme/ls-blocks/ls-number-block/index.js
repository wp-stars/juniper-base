(()=>{"use strict";var e,t,o,r,l={22210:(e,t,o)=>{o.d(t,{Z:()=>a});var r=o(69307),l=(o(99196),o(52175)),n=o(55609),s=o(65736);const a=e=>{let{attributes:t,setAttributes:o}=e;return(0,r.createElement)("div",(0,l.useBlockProps)(),(0,r.createElement)(l.InspectorControls,null,(0,r.createElement)(n.Panel,{header:"Number Block"},(0,r.createElement)(n.PanelBody,{title:"Settings",initialOpen:!0},(0,r.createElement)(n.PanelRow,null,(0,r.createElement)(l.PanelColorSettings,{title:(0,s.__)("Color Settings","iwgplating"),colorSettings:[{value:t.bgColor,onChange:e=>o({bgColor:e}),label:(0,s.__)("Background Color","iwgplating")}]}))))),(0,r.createElement)("div",{className:"wp-block-ls-number-block__colorbox",style:{backgroundColor:t.bgColor}}),(0,r.createElement)(n.TextControl,{label:(0,s.__)("Number","iwgplating"),value:t.textNumber,onChange:e=>o({textNumber:e})}))}},66580:(e,t,o)=>{o.d(t,{Z:()=>s});var r=o(69307),l=(o(99196),o(52175)),n=o(4981);const s=e=>{let{attributes:t}=e;const o=(0,n.getBlockDefaultClassName)("ls/number-block");return(0,r.createElement)("div",l.useBlockProps.save(),(0,r.createElement)("div",{className:`${o}__colorbox`,style:{backgroundColor:t.bgColor}}),(0,r.createElement)("span",{className:`${o}__number`},t.textNumber))}},99196:e=>{e.exports=window.React},52175:e=>{e.exports=window.wp.blockEditor},4981:e=>{e.exports=window.wp.blocks},55609:e=>{e.exports=window.wp.components},69307:e=>{e.exports=window.wp.element},65736:e=>{e.exports=window.wp.i18n},55741:e=>{e.exports=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"ls/number-block","version":"0.1.0","title":"Number Block","category":"ls","icon":"screenoptions","description":"Number Block","supports":{"html":false},"textdomain":"iwgplating","attributes":{"message":{"type":"string","source":"text","selector":"div","default":""},"textNumber":{"type":"string"},"bgColor":{"type":"string"}}}')}},n={};function s(e){var t=n[e];if(void 0!==t)return t.exports;var o=n[e]={exports:{}};return l[e](o,o.exports,s),o.exports}s.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return s.d(t,{a:t}),t},s.d=(e,t)=>{for(var o in t)s.o(t,o)&&!s.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},s.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),e=s(4981),t=s(66580),o=s(22210),r=s(55741),(0,e.registerBlockType)(r,{edit:o.Z,save:t.Z})})();