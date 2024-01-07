import{s as A}from"./Dashboard.e43aa3b4.js";import"./app.7301094e.js";import"./AuthenticatedLayout.ba5e32cf.js";import"./laravel-vue-pagination.es.96a3ed4c.js";var E={1:"\u09E7",2:"\u09E8",3:"\u09E9",4:"\u09EA",5:"\u09EB",6:"\u09EC",7:"\u09ED",8:"\u09EE",9:"\u09EF",0:"\u09E6"},D={"\u09E7":"1","\u09E8":"2","\u09E9":"3","\u09EA":"4","\u09EB":"5","\u09EC":"6","\u09ED":"7","\u09EE":"8","\u09EF":"9","\u09E6":"0"},F={name:"bn-bd",weekdays:"\u09B0\u09AC\u09BF\u09AC\u09BE\u09B0_\u09B8\u09CB\u09AE\u09AC\u09BE\u09B0_\u09AE\u0999\u09CD\u0997\u09B2\u09AC\u09BE\u09B0_\u09AC\u09C1\u09A7\u09AC\u09BE\u09B0_\u09AC\u09C3\u09B9\u09B8\u09CD\u09AA\u09A4\u09BF\u09AC\u09BE\u09B0_\u09B6\u09C1\u0995\u09CD\u09B0\u09AC\u09BE\u09B0_\u09B6\u09A8\u09BF\u09AC\u09BE\u09B0".split("_"),months:"\u099C\u09BE\u09A8\u09C1\u09DF\u09BE\u09B0\u09BF_\u09AB\u09C7\u09AC\u09CD\u09B0\u09C1\u09DF\u09BE\u09B0\u09BF_\u09AE\u09BE\u09B0\u09CD\u099A_\u098F\u09AA\u09CD\u09B0\u09BF\u09B2_\u09AE\u09C7_\u099C\u09C1\u09A8_\u099C\u09C1\u09B2\u09BE\u0987_\u0986\u0997\u09B8\u09CD\u099F_\u09B8\u09C7\u09AA\u09CD\u099F\u09C7\u09AE\u09CD\u09AC\u09B0_\u0985\u0995\u09CD\u099F\u09CB\u09AC\u09B0_\u09A8\u09AD\u09C7\u09AE\u09CD\u09AC\u09B0_\u09A1\u09BF\u09B8\u09C7\u09AE\u09CD\u09AC\u09B0".split("_"),weekdaysShort:"\u09B0\u09AC\u09BF_\u09B8\u09CB\u09AE_\u09AE\u0999\u09CD\u0997\u09B2_\u09AC\u09C1\u09A7_\u09AC\u09C3\u09B9\u09B8\u09CD\u09AA\u09A4\u09BF_\u09B6\u09C1\u0995\u09CD\u09B0_\u09B6\u09A8\u09BF".split("_"),monthsShort:"\u099C\u09BE\u09A8\u09C1_\u09AB\u09C7\u09AC\u09CD\u09B0\u09C1_\u09AE\u09BE\u09B0\u09CD\u099A_\u098F\u09AA\u09CD\u09B0\u09BF\u09B2_\u09AE\u09C7_\u099C\u09C1\u09A8_\u099C\u09C1\u09B2\u09BE\u0987_\u0986\u0997\u09B8\u09CD\u099F_\u09B8\u09C7\u09AA\u09CD\u099F_\u0985\u0995\u09CD\u099F\u09CB_\u09A8\u09AD\u09C7_\u09A1\u09BF\u09B8\u09C7".split("_"),weekdaysMin:"\u09B0\u09AC\u09BF_\u09B8\u09CB\u09AE_\u09AE\u0999\u09CD\u0997_\u09AC\u09C1\u09A7_\u09AC\u09C3\u09B9\u0983_\u09B6\u09C1\u0995\u09CD\u09B0_\u09B6\u09A8\u09BF".split("_"),weekStart:0,preparse:function(u){return u.replace(/[১২৩৪৫৬৭৮৯০]/g,function(B){return D[B]})},postformat:function(u){return u.replace(/\d/g,function(B){return E[B]})},ordinal:function(u){var B=["\u0987","\u09B2\u09BE","\u09B0\u09BE","\u09A0\u09BE","\u09B6\u09C7"],C=u%100;return"["+u+(B[(C-20)%10]||B[C]||B[0])+"]"},formats:{LT:"A h:mm \u09B8\u09AE\u09DF",LTS:"A h:mm:ss \u09B8\u09AE\u09DF",L:"DD/MM/YYYY \u0996\u09CD\u09B0\u09BF\u09B8\u09CD\u099F\u09BE\u09AC\u09CD\u09A6",LL:"D MMMM YYYY \u0996\u09CD\u09B0\u09BF\u09B8\u09CD\u099F\u09BE\u09AC\u09CD\u09A6",LLL:"D MMMM YYYY \u0996\u09CD\u09B0\u09BF\u09B8\u09CD\u099F\u09BE\u09AC\u09CD\u09A6, A h:mm \u09B8\u09AE\u09DF",LLLL:"dddd, D MMMM YYYY \u0996\u09CD\u09B0\u09BF\u09B8\u09CD\u099F\u09BE\u09AC\u09CD\u09A6, A h:mm \u09B8\u09AE\u09DF"},meridiem:function(u){return u<4?"\u09B0\u09BE\u09A4":u<6?"\u09AD\u09CB\u09B0":u<12?"\u09B8\u0995\u09BE\u09B2":u<15?"\u09A6\u09C1\u09AA\u09C1\u09B0":u<18?"\u09AC\u09BF\u0995\u09BE\u09B2":u<20?"\u09B8\u09A8\u09CD\u09A7\u09CD\u09AF\u09BE":"\u09B0\u09BE\u09A4"},relativeTime:{future:"%s \u09AA\u09B0\u09C7",past:"%s \u0986\u0997\u09C7",s:"\u0995\u09DF\u09C7\u0995 \u09B8\u09C7\u0995\u09C7\u09A8\u09CD\u09A1",m:"\u098F\u0995 \u09AE\u09BF\u09A8\u09BF\u099F",mm:"%d \u09AE\u09BF\u09A8\u09BF\u099F",h:"\u098F\u0995 \u0998\u09A8\u09CD\u099F\u09BE",hh:"%d \u0998\u09A8\u09CD\u099F\u09BE",d:"\u098F\u0995 \u09A6\u09BF\u09A8",dd:"%d \u09A6\u09BF\u09A8",M:"\u098F\u0995 \u09AE\u09BE\u09B8",MM:"%d \u09AE\u09BE\u09B8",y:"\u098F\u0995 \u09AC\u099B\u09B0",yy:"%d \u09AC\u099B\u09B0"}};A.locale(F,null,!0);export{F as default};
