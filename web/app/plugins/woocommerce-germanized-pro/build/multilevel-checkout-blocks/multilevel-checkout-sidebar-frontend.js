"use strict";(self.webpackWcGzdProBlocksJsonp=self.webpackWcGzdProBlocksJsonp||[]).push([[738],{604:function(e,t,r){var n=r(196),c=r(333),a=r(184),o=r.n(a);const l=e=>({thousandSeparator:e?.thousandSeparator,decimalSeparator:e?.decimalSeparator,fixedDecimalScale:!0,prefix:e?.prefix,suffix:e?.suffix,isNumericString:!0});t.Z=({className:e,value:t,currency:r,onValueChange:a,displayType:i="text",...s})=>{var u;const m="string"==typeof t?parseInt(t,10):t;if(!Number.isFinite(m))return null;const d=m/10**r.minorUnit;if(!Number.isFinite(d))return null;const p=o()("wc-block-formatted-money-amount","wc-block-components-formatted-money-amount",e),f=null!==(u=s.decimalScale)&&void 0!==u?u:r?.minorUnit,y={...s,...l(r),decimalScale:f,value:void 0,currency:void 0,onValueChange:void 0},v=a?e=>{const t=+e.value*10**r.minorUnit;a(t)}:()=>{};return(0,n.createElement)(c.Z,{className:p,displayType:i,...y,value:d,onValueChange:v})}},123:function(e,t,r){r.d(t,{e:function(){return n}});const n=e=>{if(!e?.currency_code)return{};const{currency_code:t,currency_symbol:r,currency_thousand_separator:n,currency_decimal_separator:c,currency_minor_unit:a,currency_prefix:o,currency_suffix:l}=e;return{code:t||"USD",symbol:r||"$",thousandSeparator:"string"==typeof n?n:",",decimalSeparator:"string"==typeof c?c:".",minorUnit:Number.isFinite(a)?a:2,prefix:"string"==typeof o?o:"$",suffix:"string"==typeof l?l:""}}},961:function(e,t,r){r.r(t),r.d(t,{Frontend:function(){return v},default:function(){return b}});var n=r(196),c=r(307),a=r(736),o=r(818),l=r(984),i=r(444),s=(0,c.createElement)(i.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,c.createElement)(i.Path,{d:"M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"})),u=(0,c.createElement)(i.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,c.createElement)(i.Path,{d:"M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"})),m=r(801),d=r(604),p=r(123),f=r(184),y=r.n(f);const v=({children:e,className:t})=>{const[r,i]=(0,c.useState)(!1),f=(0,c.useRef)(null),{cartTotals:v}=(0,o.useSelect)((e=>({cartTotals:e(m.CART_STORE_KEY).getCartTotals()})));(0,c.useEffect)((()=>{if(r&&null!==f.current){const e=f.current.querySelector(".wc-block-components-panel__button");e&&("true"===e.getAttribute("aria-expanded")||e.click())}}),[r,f]);const b=v.total_price;return(0,n.createElement)("div",{className:y()({"wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar":!0,"woocommerce-gzdp-multilevel-checkout-sidebar":!0,"is-open":r}),ref:f},(0,n.createElement)("div",{className:"wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar-mobile-nav"},(0,n.createElement)("button",{className:"multilevel-checkout-sidebar-nav-toggle",onClick:()=>i(!r)},(0,n.createElement)("span",{className:"sidebar-mobile-summary-text"},(0,a._x)("Order Summary","multilevel-checkout","woocommerce-germanized-pro"),(0,n.createElement)(l.Z,{icon:r?s:u})),(0,n.createElement)("span",{className:"sidebar-mobile-summary-total"},(0,n.createElement)(d.Z,{currency:(0,p.e)(v),value:b})))),e)};var b=v}}]);