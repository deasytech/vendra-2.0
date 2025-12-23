const g="modulepreload",y=function(e){return"/build/"+e},m={},c=function(i,n,d){let u=Promise.resolve();if(n&&n.length>0){let v=function(t){return Promise.all(t.map(l=>Promise.resolve(l).then(a=>({status:"fulfilled",value:a}),a=>({status:"rejected",reason:a}))))};var k=v;document.getElementsByTagName("link");const s=document.querySelector("meta[property=csp-nonce]"),o=s?.nonce||s?.getAttribute("nonce");u=v(n.map(t=>{if(t=y(t),t in m)return;m[t]=!0;const l=t.endsWith(".css"),a=l?'[rel="stylesheet"]':"";if(document.querySelector(`link[href="${t}"]${a}`))return;const r=document.createElement("link");if(r.rel=l?"stylesheet":g,l||(r.as="script"),r.crossOrigin="",r.href=t,o&&r.setAttribute("nonce",o),document.head.appendChild(r),l)return new Promise((h,w)=>{r.addEventListener("load",h),r.addEventListener("error",()=>w(new Error(`Unable to preload CSS for ${t}`)))})}))}function f(s){const o=new Event("vite:preloadError",{cancelable:!0});if(o.payload=s,window.dispatchEvent(o),!o.defaultPrevented)throw s}return u.then(s=>{for(const o of s||[])o.status==="rejected"&&f(o.reason);return i().catch(f)})};document.addEventListener("livewire:init",()=>{Livewire.on("success",e=>{p(e,"success")}),Livewire.on("error",e=>{p(e,"error")})});function p(e,i="info"){const n=document.createElement("div");n.className="fixed top-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden";const d=i==="success"?'<svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>':'<svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';n.innerHTML=`
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${d}
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">${e}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="this.closest('.fixed').remove()" class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `,document.body.appendChild(n),setTimeout(()=>{n.parentNode&&n.remove()},5e3)}typeof window.Alpine>"u"?c(async()=>{const{default:e}=await import("./module.esm-CTa8DXZh.js");return{default:e}},[]).then(({default:e})=>{c(async()=>{const{default:i}=await import("./module.esm-DKzoKYMU.js");return{default:i}},[]).then(({default:i})=>{e.plugin(i),window.Alpine=e,e.start()})}):window.Alpine&&window.Alpine.plugin&&c(async()=>{const{default:e}=await import("./module.esm-DKzoKYMU.js");return{default:e}},[]).then(({default:e})=>{window.Alpine.plugin(e)});
