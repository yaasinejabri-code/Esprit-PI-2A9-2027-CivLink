/* Espace citoyen : consulter mes factures (filtre fournisseur), simuler un paiement (choix du mode),
   historique des factures réglées et téléchargement du reçu de démonstration.
   Toutes les données viennent de la base via Controllers/FactureController.php (aucun vrai paiement). */
(function () {
 'use strict';
 const app=document.getElementById('facApp'); if(!app)return;
 const API=app.dataset.api,CSRF=app.dataset.csrf,LOGIN=app.dataset.login;
 const el=id=>document.getElementById(id);
 const esc=v=>String(v==null?'':v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const fmt=n=>Number(n).toLocaleString('fr-TN',{minimumFractionDigits:3,maximumFractionDigits:3})+' DT';
 const date=s=>s?new Date(String(s).slice(0,10)+'T12:00:00').toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'}):'—';
 let invoices=[],modes=[],selected='all',query='',active=null,recu=null,timer=null;

 /* ===== Appels serveur (fetch) ===== */
 async function call(action,params,post){
  let r;
  try{
   if(post)r=await fetch(API,{method:'POST',credentials:'same-origin',body:new URLSearchParams({action,csrf:CSRF,...params})});
   else r=await fetch(API+'?'+new URLSearchParams({action,...params}),{credentials:'same-origin',cache:'no-store'});
  }catch(_){throw new Error('Serveur injoignable : vérifiez qu’Apache et MySQL sont démarrés.');}
  let d={};try{d=await r.json();}catch(_){d={error:'Réponse invalide du serveur.'};}
  if(r.status===401){location.href=LOGIN;throw new Error(d.error||'Connexion requise.');}
  if(!r.ok)throw new Error(d.error||'Erreur serveur.');
  return d;
 }
 async function load(){
  try{
   const d=await call('lister',{fournisseur:selected==='all'?'':selected,q:query});
   invoices=d.factures.map(f=>({...f,idFacture:Number(f.idFacture),idMode:f.idMode==null?null:Number(f.idMode)}));
   modes=d.modes.map(m=>({...m,idMode:Number(m.idMode)}));
   render();
  }catch(e){el('facList').innerHTML='<p class="fac-error" style="padding:20px">'+esc(e.message)+'</p>';}
 }
 function render(){
  const unpaid=invoices.filter(x=>x.statut!=='payee'),paid=invoices.filter(x=>x.statut==='payee');
  el('statPending').textContent=unpaid.length;el('statAmount').textContent=fmt(unpaid.reduce((s,x)=>s+Number(x.montant),0));el('statPaid').textContent=paid.length;
  el('facCount').textContent=invoices.length+' facture'+(invoices.length!==1?'s':'');
  el('facEmpty').hidden=invoices.length!==0;
  el('facList').innerHTML=invoices.map(x=>{const p=x.statut==='payee';return `<article class="fac-invoice"><div class="fac-provider ${esc(x.fournisseur).toLowerCase()}"><i class="fas fa-${x.fournisseur==='STEG'?'bolt':'tint'}"></i></div><div class="fac-invoice-info"><strong>${esc(x.fournisseur)} <span aria-hidden="true">·</span> Réf. ${esc(x.reference)}</strong><p>Émise : ${date(x.dateEmission)}<span>•</span>Échéance : ${date(x.dateEcheance)}${p?`<span>•</span>${esc(x.modeLibelle||'')}`:''}</p></div><div class="fac-invoice-amount"><strong>${fmt(x.montant)}</strong><span class="fac-tag ${p?'paid':'pending'}">${p?'Payée (démo)':'Impayée'}</span></div><div class="fac-invoice-action">${p?`<button type="button" data-act="recu" data-id="${x.idFacture}" class="is-paid">Voir le reçu <i class="fas fa-file-alt"></i></button>`:`<button type="button" data-act="payer" data-id="${x.idFacture}">Simuler le paiement <i class="fas fa-arrow-right"></i></button>`}</div></article>`;}).join('');
  el('facHistory').innerHTML=paid.length?paid.map(x=>`<div class="fac-history-item"><div><strong>${esc(x.fournisseur)} · ${esc(x.reference)}</strong><small>${esc(x.referencePaiement||'')} · ${date(x.datePaiement)} · ${esc(x.modeLibelle||'')} · paiement simulé</small></div><span>${fmt(x.montant)}</span><a class="fac-dl" href="${API}?action=telecharger&id=${x.idFacture}" title="Télécharger le reçu" aria-label="Télécharger le reçu de ${esc(x.reference)}"><i class="fas fa-download"></i></a></div>`).join(''):'<p class="fac-receipt">Aucun paiement simulé pour le moment.</p>';
 }
 const byId=id=>invoices.find(x=>String(x.idFacture)===String(id));
 const showErr=(id,m)=>{const e=el(id);e.textContent=m||'';e.hidden=!m;};

 /* Modale paiement / reçu */
 function show(m,focus){el(m).hidden=false;el(focus).focus();document.body.style.overflow='hidden';}
 function hide(m){el(m).hidden=true;document.body.style.overflow='';}

 /* Procédure de paiement : 1) choix du mode (boutons radio) 2) étapes propres au mode choisi 3) reçu */
 let step='choose',modeId=null,otpSent=false;
 const kindOf=m=>{const l=(m.libelle||'').toLowerCase();return /carte/.test(l)?'carte':/virement/.test(l)?'virement':/esp[eè]ces|guichet|cash/.test(l)?'especes':/mobile|portefeuille|wallet|d17|flouci/.test(l)?'mobile':'autre';};
 const agences=['Tunis – Centre ville','Sfax – Avenue Habib Bourguiba','Sousse – Place Farhat Hached','Nabeul – Avenue Taieb Mhiri'];
 function openPay(f){active=f;recu=null;step='choose';modeId=modes.length?modes[0].idMode:null;otpSent=false;drawPay();show('facModal','facClose');}
 function modeForm(m){
  const k=kindOf(m),ref=esc(active.reference),amt=fmt(active.montant);
  if(k==='carte')return `<p class="fac-step-title"><i class="far fa-credit-card"></i> Paiement par carte bancaire</p><div class="fac-field"><label for="pNum">Numéro de carte (16 chiffres)</label><input id="pNum" inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="off"></div><div class="fac-field"><label for="pNom">Nom du titulaire</label><input id="pNom" autocomplete="off"></div><div class="fac-row"><div class="fac-field"><label for="pExp">Expiration (MM/AA)</label><input id="pExp" maxlength="5" placeholder="MM/AA" autocomplete="off"></div><div class="fac-field"><label for="pCvv">CVV</label><input id="pCvv" inputmode="numeric" maxlength="3" placeholder="123" autocomplete="off"></div></div><p class="fac-hint">Démonstration : saisissez des valeurs fictives, rien n’est conservé.</p>`;
  if(k==='virement')return `<p class="fac-step-title"><i class="fas fa-university"></i> Virement bancaire</p><div class="fac-modal-lines"><div><span>Bénéficiaire</span><strong>CivLink – Services publics (démo)</strong></div><div><span>RIB / IBAN</span><strong>TN59 0000 0000 0000 0000 0000</strong></div><div><span>Montant</span><strong>${amt}</strong></div><div><span>Motif à indiquer</span><strong>${ref}</strong></div></div><div class="fac-field" style="margin-top:14px"><label for="pBanque">Votre banque</label><input id="pBanque" autocomplete="off" placeholder="Ex. : BIAT, STB, Amen Bank…"></div><label class="fac-check"><input type="checkbox" id="pVir"> J’ai effectué le virement avec la référence ci-dessus</label>`;
  if(k==='especes')return `<p class="fac-step-title"><i class="fas fa-money-bill-wave"></i> Paiement en espèces au guichet</p><div class="fac-field"><label for="pAgence">Agence de paiement</label><select id="pAgence">${agences.map(x=>`<option>${esc(x)}</option>`).join('')}</select></div><div class="fac-modal-lines"><div><span>Code de paiement</span><strong>CASH-${ref.replace(/[^A-Z0-9]/gi,'').slice(-6).toUpperCase()}</strong></div><div><span>Montant à remettre</span><strong>${amt}</strong></div></div><label class="fac-check"><input type="checkbox" id="pCash"> Je présenterai ce code au guichet et je règle le montant</label>`;
  if(k==='mobile')return `<p class="fac-step-title"><i class="fas fa-mobile-alt"></i> Portefeuille mobile</p><div class="fac-field"><label for="pTel">Numéro de téléphone (8 chiffres)</label><input id="pTel" inputmode="numeric" maxlength="8" placeholder="2X XXX XXX" autocomplete="off"></div><button type="button" class="fac-btn fac-btn-outline" id="pSend">Envoyer le code de validation</button><div class="fac-field" id="pOtpBox" hidden style="margin-top:14px"><label for="pOtp">Code reçu par SMS</label><input id="pOtp" inputmode="numeric" maxlength="6" placeholder="6 chiffres" autocomplete="off"><p class="fac-hint">Démonstration : le code est <strong>123456</strong>.</p></div>`;
  return `<p class="fac-step-title"><i class="fas fa-check-double"></i> ${esc(m.libelle)}</p><p class="fac-hint">${esc(m.description||'Confirmez pour terminer la simulation du paiement.')}</p><label class="fac-check"><input type="checkbox" id="pOk"> Je confirme le paiement de ${amt} par « ${esc(m.libelle)} »</label>`;
 }
 function validatePay(m){
  const k=kindOf(m),v=id=>(el(id)?el(id).value.trim():'');
  if(k==='carte'){const n=v('pNum').replace(/\s/g,'');if(!/^\d{16}$/.test(n))return'Numéro de carte invalide (16 chiffres).';if(!v('pNom'))return'Saisissez le nom du titulaire.';const e=v('pExp').match(/^(\d{2})\/(\d{2})$/);if(!e||+e[1]<1||+e[1]>12)return'Date d’expiration invalide (MM/AA).';const now=new Date();if(2000+ +e[2]<now.getFullYear()||(2000+ +e[2]===now.getFullYear()&&+e[1]<now.getMonth()+1))return'Cette carte est expirée.';if(!/^\d{3}$/.test(v('pCvv')))return'CVV invalide (3 chiffres).';}
  else if(k==='virement'){if(!v('pBanque'))return'Indiquez le nom de votre banque.';if(!el('pVir').checked)return'Confirmez que le virement a été effectué.';}
  else if(k==='especes'){if(!el('pCash').checked)return'Confirmez votre passage au guichet.';}
  else if(k==='mobile'){if(!/^\d{8}$/.test(v('pTel')))return'Numéro de téléphone invalide (8 chiffres).';if(!otpSent)return'Envoyez d’abord le code de validation.';if(v('pOtp')!=='123456')return'Code de validation incorrect.';}
  else if(!el('pOk').checked)return'Confirmez le paiement.';
  return '';
 }
 function drawPay(){
  const done=!!recu;el('facModalTitle').textContent=done?'Reçu de démonstration':'Payer la facture';showErr('facError','');
  let h='';
  if(done)h+='<div class="fac-modal-success"><i class="fas fa-check-circle"></i><p>Paiement fictif confirmé. Aucun montant n’a été débité.</p></div>';
  h+=`<div class="fac-modal-lines"><div><span>Fournisseur</span><strong>${esc(active.fournisseur)}</strong></div><div><span>Référence</span><strong>${esc(active.reference)}</strong></div><div><span>Montant</span><strong>${fmt(active.montant)}</strong></div>`;
  if(done)h+=`<div><span>N° de reçu</span><strong>${esc(recu.numeroRecu)}</strong></div><div><span>Mode</span><strong>${esc(recu.mode)}</strong></div><div><span>Réf. paiement</span><strong>${esc(recu.referencePaiement)}</strong></div><div><span>Date</span><strong>${date(recu.datePaiement)}</strong></div>`;
  el('facModalDetail').innerHTML=h+'</div>';
  const box=el('facModeChoice');box.hidden=done;
  if(!done){
   const m=modes.find(x=>x.idMode===modeId);
   if(step==='choose'){
    box.innerHTML='<fieldset class="fac-radios"><legend>Quel mode de paiement voulez-vous choisir ?</legend>'+modes.map(x=>`<label class="fac-radio"><input type="radio" name="facMode" value="${x.idMode}" ${x.idMode===modeId?'checked':''}><span><strong>${esc(x.libelle)}</strong><small>${esc(x.description)}</small></span></label>`).join('')+'</fieldset>';
    box.querySelectorAll('input[name=facMode]').forEach(r=>r.addEventListener('change',()=>{modeId=Number(r.value);}));
   }else{
    box.innerHTML=m?modeForm(m):'';
    const send=el('pSend');if(send)send.addEventListener('click',()=>{if(!/^\d{8}$/.test(el('pTel').value.trim())){showErr('facError','Numéro de téléphone invalide (8 chiffres).');return;}showErr('facError','');otpSent=true;el('pOtpBox').hidden=false;el('pOtp').focus();});
   }
  }
  el('facConfirm').hidden=done;el('facConfirm').innerHTML=step==='choose'?'Continuer <i class="fas fa-arrow-right"></i>':'Confirmer le paiement <i class="fas fa-check"></i>';
  const back=el('facCancelPay');back.hidden=done||step==='choose';back.textContent='Retour';
  el('facCancel').textContent=done?'Fermer':'Annuler';
  const dl=el('facDownload');dl.hidden=!done;if(done)dl.href=API+'?action=telecharger&id='+encodeURIComponent(recu.idFacture||active.idFacture);
 }


 document.querySelectorAll('.fac-tabs button').forEach(b=>b.addEventListener('click',()=>{selected=b.dataset.provider;document.querySelectorAll('.fac-tabs button').forEach(t=>{const a=t===b;t.classList.toggle('is-active',a);t.setAttribute('aria-pressed',String(a));});load();}));
 el('facSearch').addEventListener('input',e=>{query=e.target.value.trim();clearTimeout(timer);timer=setTimeout(load,250);});
 el('facList').addEventListener('click',async e=>{
  const b=e.target.closest('button[data-act]');if(!b)return;const f=byId(b.dataset.id);if(!f)return;
  try{
   if(b.dataset.act==='payer'){if(!modes.length){alert('Aucun mode de paiement disponible. Contactez un administrateur.');return;}openPay(f);}
   else if(b.dataset.act==='recu'){const d=await call('recu',{id:f.idFacture});active=f;recu=d.recu;drawPay();show('facModal','facClose');}
  }catch(err){alert(err.message);}
 });
 el('facCancelPay').addEventListener('click',()=>{step='choose';otpSent=false;drawPay();});
 el('facConfirm').addEventListener('click',async()=>{
  if(!active)return;
  if(step==='choose'){if(modeId==null){showErr('facError','Choisissez un mode de paiement.');return;}step='detail';drawPay();return;}
  const m=modes.find(x=>x.idMode===modeId),msg=m?validatePay(m):'Mode introuvable.';if(msg){showErr('facError',msg);return;}
  try{const d=await call('simulerPaiement',{id:active.idFacture,idMode:modeId},true);active={...active,...d.facture,idFacture:Number(d.facture.idFacture)};recu=d.recu;drawPay();await load();}
  catch(err){showErr('facError',err.message);}
 });
 ['facClose','facCancel'].forEach(i=>el(i).addEventListener('click',()=>{hide('facModal');active=null;recu=null;}));
 document.querySelectorAll('.fac-modal-scrim').forEach(s=>s.addEventListener('click',()=>{hide('facModal');active=null;recu=null;}));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'){hide('facModal');active=null;recu=null;}});
 load();
})();
