(function(){'use strict';var C=window.ABCS||{};var modal=document.getElementById('abcs-modal');function open(e){if(e){e.preventDefault()}
if(!modal){return}
var need=modal.querySelector('[name=product_needed]');if(need&&!need.value&&C.product){need.value=C.product}
if(typeof modal.showModal==='function'){modal.showModal()}else{modal.setAttribute('open','')}
var f=modal.querySelector('[name=last_name]');if(f){setTimeout(function(){f.focus()},50)}}
function close(){if(modal){if(modal.close){modal.close()}else{modal.removeAttribute('open')}}}
document.addEventListener('click',function(e){var t=e.target.closest('[data-abed-consult-open], a[href="#consult"]');if(t){open(e);return}
if(e.target.closest('[data-abcs-close]')||e.target===modal){close()}});document.querySelectorAll('[data-abcs-form]').forEach(function(f){var need=f.querySelector('[name=product_needed]');if(need&&!need.value&&C.product&&f.closest('.abcs-inline')){need.placeholder=C.product}
f.addEventListener('submit',function(e){e.preventDefault();var msg=f.querySelector('.abcs-msg');var btn=f.querySelector('.abcs-submit');var fd=new FormData(f);fd.append('action','abed_consult_submit');fd.append('page_url',location.href);fd.append('pid',C.pid||0);btn.disabled=!0;msg.className='abcs-msg';msg.textContent='در حال ثبت…';fetch(C.ajax,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){btn.disabled=!1;msg.textContent=(j&&j.data&&j.data.message)||'خطا؛ لطفاً تماس بگیرید.';msg.className='abcs-msg '+(j&&j.success?'is-ok':'is-err');if(j&&j.success){f.reset();f.classList.add('is-done')}}).catch(function(){btn.disabled=!1;msg.textContent='ارتباط برقرار نشد؛ دوباره تلاش کنید.';msg.className='abcs-msg is-err'})})})})()
;