"use strict";(self.webpackWcGzdProBlocksJsonp=self.webpackWcGzdProBlocksJsonp||[]).push([[738],{169:function(e,t,c){c.r(t),c.d(t,{Frontend:function(){return k}});var l=c(196),r=c(307),a=c(736),o=c(818),n=c(984),s=c(15),m=c(904),u=c(801),i=c(604),d=c(123),p=c(184),b=c.n(p);const k=({children:e,className:t})=>{const[c,p]=(0,r.useState)(!1),k=(0,r.useRef)(null),{cartTotals:v}=(0,o.useSelect)((e=>({cartTotals:e(u.CART_STORE_KEY).getCartTotals()})));(0,r.useEffect)((()=>{if(c&&null!==k.current){const e=k.current.querySelector(".wc-block-components-panel__button");e&&("true"===e.getAttribute("aria-expanded")||e.click())}}),[c,k]);const f=v.total_price;return(0,l.createElement)("div",{className:b()({"wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar":!0,"woocommerce-gzdp-multilevel-checkout-sidebar":!0,"is-open":c}),ref:k},(0,l.createElement)("div",{className:"wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar-mobile-nav"},(0,l.createElement)("button",{className:"multilevel-checkout-sidebar-nav-toggle",onClick:()=>p(!c)},(0,l.createElement)("span",{className:"sidebar-mobile-summary-text"},(0,a._x)("Order Summary","multilevel-checkout","woocommerce-germanized-pro"),(0,l.createElement)(n.Z,{icon:c?s.Z:m.Z})),(0,l.createElement)("span",{className:"sidebar-mobile-summary-total"},(0,l.createElement)(i.Z,{currency:(0,d.e)(v),value:f})))),e)};t.default=k}}]);