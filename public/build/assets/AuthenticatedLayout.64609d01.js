import{p as E,q as j,c as b,m as $,o as c,a as h,h as t,r as g,w as L,l as B,f as a,e as o,n as m,u as p,T as z,d as x,L as M,g as _,s as N,j as n,t as i}from"./app.1790b794.js";const S=(d,s)=>{const l=d.__vccOpts||d;for(const[u,e]of s)l[u]=e;return l},T={class:"relative"},D={__name:"Dropdown",props:{align:{default:"right"},width:{default:"48"},contentClasses:{default:()=>["py-1","bg-white "]}},setup(d){const s=d,l=f=>{r.value&&f.key==="Escape"&&(r.value=!1)};E(()=>document.addEventListener("keydown",l)),j(()=>document.removeEventListener("keydown",l));const u=b(()=>({48:"w-48"})[s.width.toString()]),e=b(()=>s.align==="left"?"origin-top-left left-0":s.align==="right"?"origin-top-right right-0":"origin-top"),r=$(!1);return(f,v)=>(c(),h("div",T,[t("div",{onClick:v[0]||(v[0]=C=>r.value=!r.value)},[g(f.$slots,"trigger")]),L(t("div",{class:"fixed inset-0 z-40",onClick:v[1]||(v[1]=C=>r.value=!1)},null,512),[[B,r.value]]),a(z,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"transform opacity-0 scale-95","enter-to-class":"transform opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"transform opacity-100 scale-100","leave-to-class":"transform opacity-0 scale-95"},{default:o(()=>[L(t("div",{class:m(["absolute z-50 mt-2 rounded-md shadow-lg",[p(u),p(e)]]),style:{display:"none"},onClick:v[2]||(v[2]=C=>r.value=!1)},[t("div",{class:m(["rounded-md ring-1 ring-black ring-opacity-5",d.contentClasses])},[g(f.$slots,"content")],2)],2),[[B,r.value]])]),_:3})]))}},w={__name:"DropdownLink",setup(d){return(s,l)=>(c(),x(p(M),{class:"block w-full px-4 py-2 border dark:border-gray-900 text-left text-sm leading-5 text-gray-700 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"},{default:o(()=>[g(s.$slots,"default")]),_:3}))}},y={__name:"NavLink",props:["href","active"],setup(d){const s=d,l=b(()=>s.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 dark:text-gray-500 focus:outline-none focus:border-indigo-700 transition  duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out");return(u,e)=>(c(),x(p(M),{href:d.href,class:m(p(l))},{default:o(()=>[g(u.$slots,"default")]),_:3},8,["href","class"]))}},k={__name:"ResponsiveNavLink",props:["href","active"],setup(d){const s=d,l=b(()=>s.active?"block pl-3 pr-4 py-2 border-l-4 border-indigo-400 text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out":"block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out");return(u,e)=>(c(),x(p(M),{href:d.href,class:m(p(l))},{default:o(()=>[g(u.$slots,"default")]),_:3},8,["href","class"]))}},V={data(){return{darkMode:!1}},created(){this.darkMode=localStorage.getItem("darkMode")==="true",this.darkMode&&document.documentElement.classList.add("dark")},methods:{toggleDarkMode(){this.darkMode=!this.darkMode,this.darkMode?document.documentElement.classList.add("dark"):document.documentElement.classList.remove("dark"),localStorage.setItem("darkMode",this.darkMode)}},computed:{iconClass(){return this.darkMode?"fas fa-sun":"fas fa-moon"}}},A={key:0,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},I=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"},null,-1),R=[I],F={key:1,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},O=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"},null,-1),q=[O];function H(d,s,l,u,e,r){return c(),h("button",{onClick:s[0]||(s[0]=(...f)=>r.toggleDarkMode&&r.toggleDarkMode(...f)),class:"px-2 py-1 rounded-full focus:outline-none dark:text-gray-400"},[e.darkMode?_("",!0):(c(),h("svg",A,R)),e.darkMode?(c(),h("svg",F,q)):_("",!0)])}const U=S(V,[["render",H]]),G={class:"min-h-screen bg-gray-100 dark:bg-gray-800"},J={class:"bg-white border-gray-100 dark:bg-gray-900"},K={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"},P={class:"flex justify-between h-16"},Q={class:"flex"},W={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},X={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Y={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Z={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},ee={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},te={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},se={class:"hidden sm:flex sm:items-center sm:ml-6"},oe={class:"ml-3 relative"},re={class:"inline-flex rounded-md"},ae={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},ne=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),ie={class:"ml-3 relative"},de={class:"inline-flex rounded-md"},le={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},ue=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),ce={class:"-mr-2 flex items-center sm:hidden"},fe={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},he={class:"pt-2 pb-3 space-y-1"},me={class:"pt-4 pb-1 border-t border-gray-200"},ge={class:"px-4"},pe={class:"font-medium text-base text-gray-800"},ve={class:"font-medium text-sm text-gray-500"},ye={class:"mt-3 space-y-1"},_e={key:0,class:"bg-white shadow dark:bg-gray-900 dark:text-gray-200"},ke={class:"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"},be={class:"dark:bg-gray-800"},we={__name:"AuthenticatedLayout",setup(d){const s=$(!1),l=N();$("en");const u=e=>{l.locale.value=e};return(e,r)=>(c(),h("div",null,[t("div",G,[t("nav",J,[t("div",K,[t("div",P,[t("div",Q,[_("",!0),t("div",W,[a(y,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:o(()=>[n(i(e.$t("home")),1)]),_:1},8,["href","active"])]),t("div",X,[a(y,{href:e.route("users.index"),active:e.route().current("users.index")},{default:o(()=>[n(i(e.$t("users")),1)]),_:1},8,["href","active"])]),t("div",Y,[a(y,{href:e.route("FormRegistrationCompleted"),active:e.route().current("FormRegistrationCompleted")},{default:o(()=>[n(i(e.$t("allCars")),1)]),_:1},8,["href","active"])]),t("div",Z,[a(y,{href:e.route("clients"),active:e.route().current("clients")},{default:o(()=>[n(i(e.$t("clients")),1)]),_:1},8,["href","active"])]),t("div",ee,[a(y,{href:e.route("transfers"),active:e.route().current("transfers")},{default:o(()=>[n(i(e.$t("accounts")),1)]),_:1},8,["href","active"])]),t("div",te,[a(y,{href:e.route("carConfig"),active:e.route().current("carConfig")},{default:o(()=>[n(i(e.$t("carTypes")),1)]),_:1},8,["href","active"])])]),t("div",se,[t("div",oe,[a(D,{align:"right",width:"48"},{trigger:o(()=>[t("span",re,[t("button",ae,[n(i(e.$t("lang"))+" ",1),ne])])]),content:o(()=>[a(w,{onClick:r[0]||(r[0]=f=>u("ar")),as:"button"},{default:o(()=>[n(" \u0639\u0631\u0628\u064A ")]),_:1}),a(w,{onClick:r[1]||(r[1]=f=>u("kr")),as:"button"},{default:o(()=>[n(" \u0643\u0631\u062F\u064A ")]),_:1})]),_:1})]),t("div",ie,[a(D,{align:"right",width:"48"},{trigger:o(()=>[t("span",de,[t("button",le,[n(i(e.$page.props.auth.user.name)+" ",1),ue])])]),content:o(()=>[a(w,{href:e.route("logout"),method:"post",as:"button"},{default:o(()=>[n(i(e.$t("logout")),1)]),_:1},8,["href"])]),_:1})]),a(U)]),t("div",ce,[t("button",{onClick:r[2]||(r[2]=f=>s.value=!s.value),class:"inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"},[(c(),h("svg",fe,[t("path",{class:m({hidden:s.value,"inline-flex":!s.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),t("path",{class:m({hidden:!s.value,"inline-flex":s.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),t("div",{class:m([{block:s.value,hidden:!s.value},"sm:hidden"])},[t("div",he,[a(k,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:o(()=>[n(i(e.$t("dashboard")),1)]),_:1},8,["href","active"])]),t("div",me,[t("div",ge,[t("div",pe,i(e.$page.props.auth.user.name),1),t("div",ve,i(e.$page.props.auth.user.email),1)]),t("div",ye,[a(k,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:o(()=>[n(i(e.$t("home")),1)]),_:1},8,["href","active"]),e.$page.props.auth.user.type_id==1?(c(),x(k,{key:0,href:e.route("users.index"),active:e.route().current("users.index")},{default:o(()=>[n(i(e.$t("users")),1)]),_:1},8,["href","active"])):_("",!0),a(k,{href:e.route("logout"),method:"post",as:"button"},{default:o(()=>[n(i(e.$t("logout")),1)]),_:1},8,["href"])])])],2)]),e.$slots.header?(c(),h("header",_e,[t("div",ke,[g(e.$slots,"header")])])):_("",!0),t("main",be,[g(e.$slots,"default")])])]))}};export{we as _,S as a};
