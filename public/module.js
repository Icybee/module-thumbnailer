!function(){var e=new function(){this.RESIZE_NONE="none",this.RESIZE_FIT="fit",this.RESIZE_FILL="fill",this.RESIZE_FIXED_HEIGHT="fixed-height",this.RESIZE_FIXED_HEIGHT_CROPPED="fixed-height-cropped",this.RESIZE_FIXED_WIDTH="fixed-width",this.RESIZE_FIXED_WIDTH_CROPPED="fixed-width-cropped",this.RESIZE_SURFACE="surface",this.RESIZE_SIMPLE="simple",this.RESIZE_CONSTRAINED="constrained",this.assertSize=function(e,t,i){switch(i){case this.RESIZE_FIXED_WIDTH:if(!e)throw new Error("Width is required for the "+i+" resize method.");break;case this.RESIZE_FIXED_HEIGHT:if(!t)throw new Error("Height is required for the "+i+" resize method.");break;default:if(!e||!t)throw new Error("Both width and height are required for the "+i+" resize method.")}return!0}},t=function(){function e(e){var t={},i=e,r=e.indexOf("?");if(r>-1){i=e.substring(0,r);var a=e.substring(r+1);t=String.parseQueryString(a),t=n.widen(t)}t=Object.merge({width:null,height:null,method:null,format:null},t);var h=i.match(/\/?(\d+x\d+|\d+x|x\d+)(\/([^\/\.]+))?(\.([a-z]+))?/);if(h){var o=h[1].split("x"),s=o[0],l=o[1];s&&(t.width=parseInt(s)),l&&(t.height=parseInt(l)),h[3]&&(t.method=h[3]),h[5]&&(t.format=h[5]),t.format&&"jpg"==t.format&&(t.format="jpeg")}return Object.filter(t,function(e){return!!e})}var t={background:null,"default":null,filter:null,format:null,height:null,method:"fill","no-interlace":!1,"no-upscale":!1,overlay:null,path:null,quality:90,src:null,width:null},i={b:"background",d:"default",ft:"filter",f:"format",h:"height",m:"method",ni:"no-interlace",nu:"no-upscale",o:"overlay",p:"path",q:"quality",s:"src",v:"version",w:"width"},n=function(e){this.options=e};return n.prototype={toString:function(){return n.serialize(this.options)}},n.defaults=t,n.shorthands=i,n.widen=function(e){var t={},n=i;return Object.each(e,function(e,i){i in n&&(i=n[i]),t[i]=e}),t},n.shorten=function(e){var t={};return Object.each(e,function(e,n){var r=Object.keyOf(i,n);r&&(n=r),t[n]=e}),t},n.normalize=function(e){var n=t,r=Object.keys(n),a=i,h=Object.clone(n);Object.each(e,function(e,t){e&&(a[t]&&(t=a[t]),h[t]=e)});var o="method"in h?h.method:null,s="width"in h?h.width:null,l="height"in h?h.height:null;return s&&l&&!o?o="fill":s&&!l?o="fixed-width":!s&&l&&(o="fixed-height"),o?h.method=o:delete h.method,Object.filter(h,function(e,t){return Object.contains(r,t)})},n.filter=function(e){var i=t,r=(Object.keys(i),n.normalize(e)),a="width"in r?r.width:null,h="height"in r?r.height:null,o="method"in r?r.method:null;return(a&&h&&"fill"===o||a&&!h&&"fixed-width"===o||!a&&h&&"fixed-height"===o)&&delete r.method,Object.filter(r,function(e,t){return e&&e!=i[t]})},n.serialize=function(e){e=n.filter(e),e=n.shorten(e),e=Object.merge({w:"",h:"",m:null,f:null},e);var t="",i=e.w,r=e.h,a=e.m,h=e.f;(i||r)&&(t=i+"x"+r,a&&(t+="/"+a),h&&(t+="."+h)),delete e.w,delete e.h,delete e.m,delete e.f;var o=Object.toQueryString(e);return o&&(t+="?"+o),t},n.unserialize=function(t){return options=e(t),options=n.filter(options)},n}(),i=new Class({options:{},initialize:function(e,t){this.src=e,this.options=t},toString:function(){var e=this.src,i=this.options,n=e.match(/repository\/files\/image\/(\d+)/)||e.match(/api\/images\/(\d+)/),r="/api/thumbnail";return"string"===typeOf(i)&&(i=t.unserialize(i)),n?r="/api/images/"+n[1]:i=Object.merge({src:e},i),r+"/"+t.serialize(i)}}),n={Image:e,Thumbnail:i,Version:t};try{module.exports=n}catch(r){ICanBoogie.Modules.Thumbnailer=n}}(),Brickrouge.Widget.AdjustThumbnailOptions=function(){var e=ICanBoogie.Modules.Thumbnailer.Version;return e.defaults.lightbox=null,e.shorthands.lb="lightbox",new Class({Implements:[Events],initialize:function(t){this.element=t=document.id(t),this.controls={},Object.each(e.defaults,function(e,i){var n=t.getElement('[name="'+i+'"]');n&&(this[i]=n)},this.controls),t.addEvent("change",this.onChange.bind(this)),this.checkMethod(),this.checkQuality()},checkMethod:function(){var e=this.controls.height,t=this.controls.width;switch(this.controls.method.get("value")){case"fixed-height":e.readOnly=!1,t.readOnly=!0;break;case"fixed-width":e.readOnly=!0,t.readOnly=!1;break;default:t.readOnly=!1,e.readOnly=!1}},checkQuality:function(){var e=this.controls.format.get("value");this.controls.quality.getParent().setStyle("display","jpeg"!=e?"none":"")},getValue:function(){var e=this.element.toQueryString().parseQueryString();return this.controls.width.readOnly&&delete e.width,this.controls.height.readOnly&&delete e.height,e},setValue:function(t){Object.each(e.normalize(t),function(e,t){var i=this[t];i&&("checkbox"==i.type?i.set("checked",!!e):i.set("value",e))},this.controls),this.checkMethod(),this.checkQuality()},onChange:function(){this.checkMethod(),this.checkQuality(),this.fireEvent("change",{target:this,values:this.getValue()})}})}(),!function(){var e=ICanBoogie.Modules.Thumbnailer.Version;Brickrouge.Widget.AdjustThumbnailVersion=new Class({Implements:[Options,Events],initialize:function(e){function t(){switch(a.get("value")){case"fixed-height":r.readOnly=!1,n.readOnly=!0;break;case"fixed-width":r.readOnly=!0,n.readOnly=!1;break;default:r.readOnly=!1,n.readOnly=!1}}function i(){var e=h.get("value");o.getParent().setStyle("display","jpeg"!=e?"none":"")}this.element=e=document.id(e);var n=e.getElement('input[name="width"]')||e.getElement('input[name$="[width]"]'),r=e.getElement('input[name="height"]')||e.getElement('input[name$="[height]"]'),a=e.getElement('select[name="method"]')||e.getElement('select[name$="[method]"]'),h=e.getElement('select[name="format"]')||e.getElement('select[name$="[format]"]'),o=e.getElement('input[name="quality"]')||e.getElement('input[name$="[quality]"]');this.elements={width:n,height:r,method:a,format:h,quality:o,"no-upscale":e.getElement('input[name="no-upscale"]')||e.getElement('input[name$="[no-upscale]"]'),background:e.getElement('input[name="background"]')||e.getElement('input[name$="[background]"]'),filter:e.getElement('input[name="filter"]')||e.getElement('input[name$="[filter]"]')},Object.each(this.elements,function(e){e&&e.addEvent("change",this.fireChange.bind(this))},this),t(),i(),a.addEvent("change",t),h.addEvent("change",i)},fireChange:function(){},setValue:function(t){var i=e.normalize(e.unserialize(t));Object.each(i,function(e,t){this.elements[t]&&("no-upscale"==t||"no-interlace"==t?this.elements[t].set("checked",e):this.elements[t].set("value",e))},this)},getValue:function(){var t=this.element.toQueryString().parseQueryString();return e.serialize(t)}})}(),Brickrouge.Widget.PopThumbnailVersion=new Class({Extends:Brickrouge.Widget.Spinner,initialize:function(e,t){this.parent(e,t),this.control=this.element.getElement("input")},open:function(){this.resetValue=this.getValue(),this.popover?(this.popover.adjust.setValue(this.resetValue),this.popover.show()):new Request.Widget("adjust-thumbnail-version/popup",function(e){this.attachAdjust(e),this.popover.show(),this.popover.addEvent("action",this.onAction.bind(this))}.bind(this)).get({value:this.getValue()})},attachAdjust:function(e){this.popover=new Icybee.Widget.AdjustPopover(e,{anchor:this.element})},change:function(){},onAction:function(e){switch(e.action){case"use":this.setValue(e.popover.adjust.getValue());break;case"remove":this.setValue(null);break;case"cancel":this.setValue(this.resetValue)}this.popover.hide()}});