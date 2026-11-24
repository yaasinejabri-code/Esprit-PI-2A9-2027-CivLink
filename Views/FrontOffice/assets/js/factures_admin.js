/* Administration : CRUD factures (sélection du citoyen, filtres, annulation de paiement)
   et CRUD modes de paiement. Validation côté client = miroir de FactureController.php
   (le serveur revalide toujours : la validation JS n'est qu'un confort de saisie). */
(function () {
 'use strict';
 const app=document.getElementById('faApp'); if(!app)return;
 const API=app.dataset.api,CSRF=app.dataset.csrf;
 const $=id=>document.getElementById(id);
 const esc=v=>String(v==null?'':v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const fmt=n=>Number(n).toLocaleString('fr-TN',{minimumFractionDigits:3,maximumFractionDigits:3})+' DT';
 const dt=s=>s?new Date(String(s).slice(0,10)+'T12:00:00').toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'}):'—';
 let invoices=[],modes=[],citoyens=[],prov='all',statut='',q='',timer=null,toastT=null;

 /* ===== réseau ===== */
 async function call(action,params,post){
  let r;
  try{
   r=post?await fetch(API,{method:'POST',credentials:'same-origin',body:new URLSearchParams({action,csrf:CSRF,...params})})
         :await fetch(API+'?'+new URLSearchParams({action,...params}),{credentials:'same-origin',cache:'no-store'});
  }catch(_){throw Object.assign(new Error('Serveur injoignable : vérifiez qu’Apache et MySQL sont démarrés.'),{});}
  let d={};try{d=await r.json();}catch(_){d={error:'Réponse invalide du serveur.'};}
  if(!r.ok)throw Object.assign(new Error(d.error||'Erreur serveur.'),{champ:d.champ});
  return d;
 }
 function toast(msg,err){const t=$('faToast');t.textContent=msg;t.className='fa-toast'+(err?' err':'');t.hidden=false;clearTimeout(toastT);toastT=setTimeout(()=>{t.hidden=true;},3500);}

 async function load(){
  try{
   const d=await call('adminLister',{fournisseur:prov==='all'?'':prov,statut,q});
   invoices=d.factures;modes=d.modes;citoyens=d.citoyens;
   renderF();renderM();renderStats();
  }catch(e){$('fBody').innerHTML='<tr><td colspan="8" class="fac-error">'+esc(e.message)+'</td></tr>';}
 }

 /* ===== rendu ===== */
 function renderStats(){
  const un=invoices.filter(x=>x.statut!=='payee'),pa=invoices.filter(x=>x.statut==='payee'),sum=a=>a.reduce((s,x)=>s+Number(x.montant),0);
  $('sUnpaid').textContent=un.length;$('sUnpaidAmt').textContent=fmt(sum(un));
  $('sPaid').textContent=pa.length;$('sPaidAmt').textContent=fmt(sum(pa));
  $('sModes').textContent=modes.length;
 }
 function renderF(){
  $('fEmpty').hidden=invoices.length!==0;
  $('fBody').innerHTML=invoices.map(x=>{
   const p=x.statut==='payee';
   return `<tr><td><strong>${esc(x.reference)}</strong></td><td>${esc(x.prenom)} ${esc(x.nom)}<small>${esc(x.email)}</small></td>
    <td><span class="fa-prov ${esc(x.fournisseur).toLowerCase()}"><i class="fas fa-${x.fournisseur==='STEG'?'bolt':'tint'}"></i> ${esc(x.fournisseur)}</span></td>
    <td>${dt(x.dateEmission)}</td><td>${dt(x.dateEcheance)}</td><td class="num"><strong>${fmt(x.montant)}</strong></td>
    <td><span class="fac-tag ${p?'paid':'pending'}">${p?'Payée (démo)':'Impayée'}</span>${p?`<small>${esc(x.modeLibelle||'')} · ${dt(x.datePaiement)}</small>`:''}</td>
    <td class="act">${p
      ?`<button type="button" class="fa-btn" data-a="annuler" data-id="${x.idFacture}" title="Annuler le paiement simulé : la facture redevient impayée"><i class="fas fa-undo"></i> Annuler le paiement</button>`
      :`<button type="button" class="fa-btn" data-a="modifier" data-id="${x.idFacture}"><i class="fas fa-pen"></i> Modifier</button><button type="button" class="fa-btn danger" data-a="supprimer" data-id="${x.idFacture}"><i class="fas fa-trash"></i> Supprimer</button>`}</td></tr>`;
  }).join('');
 }
 function renderM(){
  $('mEmpty').hidden=modes.length!==0;
  $('mBody').innerHTML=modes.map(m=>{const n=Number(m.nbFactures);return `<tr><td><strong>${esc(m.libelle)}</strong></td><td>${esc(m.description)||'<small>—</small>'}</td><td class="num">${n}</td>
   <td class="act"><button type="button" class="fa-btn" data-m="modifier" data-id="${m.idMode}"><i class="fas fa-pen"></i> Modifier</button><button type="button" class="fa-btn danger" data-m="supprimer" data-id="${m.idMode}" ${n?'disabled title="Utilisé par '+n+' facture(s) : suppression impossible"':''}><i class="fas fa-trash"></i> Supprimer</button></td></tr>`;}).join('');
 }

 /* ===== modales & validation ===== */
 const open=id=>{$(id).hidden=false;document.body.style.overflow='hidden';const f=$(id).querySelector('input:not([type=hidden]),select');if(f)f.focus();};
 const close=id=>{$(id).hidden=true;document.body.style.overflow='';};
 const setErr=(form,champ,msg)=>{const s=form.querySelector('.fa-err[data-for="'+champ+'"]');if(s)s.textContent=msg||'';const i=form.elements[champ];if(i)i.setAttribute('aria-invalid',msg?'true':'false');};
 const clearErrs=form=>{form.querySelectorAll('.fa-err').forEach(s=>{s.textContent='';});[...form.elements].forEach(i=>i.removeAttribute&&i.removeAttribute('aria-invalid'));};
 const validDate=s=>{if(!/^\d{4}-\d{2}-\d{2}$/.test(s))return false;const d=new Date(s+'T00:00:00Z');return !isNaN(d)&&d.toISOString().slice(0,10)===s;};

 function checkFacture(f){
  const e={},v=n=>f.elements[n].value.trim();
  if(!/^[1-9]\d*$/.test(v('idUtilisateur')))e.idUtilisateur='Sélectionnez un citoyen.';
  if(!/^[A-Za-z0-9._\/-]{3,60}$/.test(v('reference')))e.reference='3 à 60 caractères : lettres, chiffres, . _ / -';
  if(!['STEG','SONEDE'].includes(v('fournisseur')))e.fournisseur='Choisissez STEG ou SONEDE.';
  const m=v('montant').replace(',','.');
  if(!/^\d{1,7}(\.\d{1,3})?$/.test(m)||Number(m)<=0)e.montant='Nombre positif, 3 décimales maximum.';
  if(!validDate(v('dateEmission')))e.dateEmission='Date invalide.';
  if(!validDate(v('dateEcheance')))e.dateEcheance='Date invalide.';
  else if(validDate(v('dateEmission'))&&v('dateEcheance')<v('dateEmission'))e.dateEcheance='L’échéance doit suivre l’émission.';
  return e;
 }
 function checkMode(f){
  const e={},l=f.elements.libelle.value.trim(),d=f.elements.description.value.trim();
  if(l.length<2||l.length>80)e.libelle='2 à 80 caractères.';
  else if(modes.some(m=>m.libelle.toLowerCase()===l.toLowerCase()&&String(m.idMode)!==f.elements.id.value))e.libelle='Ce libellé existe déjà.';
  if(d.length>255)e.description='255 caractères maximum.';
  return e;
 }
 function showErrors(form,errs,box){clearErrs(form);Object.entries(errs).forEach(([k,m])=>setErr(form,k,m));const first=Object.keys(errs)[0];if(first&&form.elements[first])form.elements[first].focus();if(box)box.hidden=true;}

 function openFacture(f){
  const form=$('fForm');form.reset();clearErrs(form);$('fFormError').hidden=true;
  $('ffCit').innerHTML='<option value="">— Sélectionner le citoyen —</option>'+citoyens.map(c=>`<option value="${c.idUtilisateur}">${esc(c.nom)} ${esc(c.prenom)} — ${esc(c.email)}</option>`).join('');
  form.elements.id.value=f?f.idFacture:'';$('fModalTitle').textContent=f?'Modifier la facture':'Ajouter une facture';
  if(f){form.elements.idUtilisateur.value=f.idUtilisateur;form.elements.reference.value=f.reference;form.elements.fournisseur.value=f.fournisseur;form.elements.montant.value=Number(f.montant).toFixed(3);form.elements.dateEmission.value=String(f.dateEmission).slice(0,10);form.elements.dateEcheance.value=String(f.dateEcheance).slice(0,10);}
  else{const t=new Date().toISOString().slice(0,10);form.elements.dateEmission.value=t;form.elements.dateEcheance.value=t;}
  open('fModal');
 }
 function openMode(m){
  const form=$('mForm');form.reset();clearErrs(form);$('mFormError').hidden=true;
  form.elements.id.value=m?m.idMode:'';$('mModalTitle').textContent=m?'Modifier le mode':'Ajouter un mode';
  if(m){form.elements.libelle.value=m.libelle;form.elements.description.value=m.description;}
  open('mModal');
 }
 async function submit(form,errBoxId,btnId,actionAdd,actionEdit,modalId,okMsg,check,fields){
  const errs=check(form);if(Object.keys(errs).length){showErrors(form,errs);return;}
  clearErrs(form);const box=$(errBoxId),btn=$(btnId);box.hidden=true;btn.disabled=true;
  const p={};fields.forEach(n=>{p[n]=form.elements[n].value.trim();});const id=form.elements.id.value;if(id)p.id=id;
  try{await call(id?actionEdit:actionAdd,p,true);close(modalId);toast(id?okMsg[1]:okMsg[0]);await load();}
  catch(err){if(err.champ&&form.elements[err.champ])setErr(form,err.champ,err.message);else{box.textContent=err.message;box.hidden=false;}}
  finally{btn.disabled=false;}
 }

 /* ===== événements ===== */
 document.querySelectorAll('.fa-tabs button').forEach(b=>b.addEventListener('click',()=>{
  document.querySelectorAll('.fa-tabs button').forEach(t=>{const a=t===b;t.classList.toggle('is-active',a);t.setAttribute('aria-selected',String(a));});
  $('panelF').hidden=b.dataset.tab!=='F';$('panelM').hidden=b.dataset.tab!=='M';}));
 document.querySelectorAll('#fProv button').forEach(b=>b.addEventListener('click',()=>{prov=b.dataset.provider;document.querySelectorAll('#fProv button').forEach(t=>{const a=t===b;t.classList.toggle('is-active',a);t.setAttribute('aria-pressed',String(a));});load();}));
 $('fStatut').addEventListener('change',e=>{statut=e.target.value;load();});
 $('fSearch').addEventListener('input',e=>{q=e.target.value.trim();clearTimeout(timer);timer=setTimeout(load,250);});
 $('fAdd').addEventListener('click',()=>openFacture(null));
 $('mAdd').addEventListener('click',()=>openMode(null));

 $('fBody').addEventListener('click',async e=>{
  const b=e.target.closest('button[data-a]');if(!b)return;const f=invoices.find(x=>String(x.idFacture)===b.dataset.id);if(!f)return;
  try{
   if(b.dataset.a==='modifier')openFacture(f);
   else if(b.dataset.a==='supprimer'){if(confirm('Supprimer la facture '+f.reference+' ?')){await call('adminSupprimer',{id:f.idFacture},true);toast('Facture supprimée.');await load();}}
   else if(b.dataset.a==='annuler'){if(confirm('Annuler le paiement simulé de '+f.reference+' ? La facture redeviendra impayée.')){await call('adminAnnulerPaiement',{id:f.idFacture},true);toast('Paiement annulé : facture impayée.');await load();}}
  }catch(err){toast(err.message,true);await load();}
 });
 $('mBody').addEventListener('click',async e=>{
  const b=e.target.closest('button[data-m]');if(!b||b.disabled)return;const m=modes.find(x=>String(x.idMode)===b.dataset.id);if(!m)return;
  try{
   if(b.dataset.m==='modifier')openMode(m);
   else if(confirm('Supprimer le mode « '+m.libelle+' » ?')){await call('modeSupprimer',{id:m.idMode},true);toast('Mode supprimé.');await load();}
  }catch(err){toast(err.message,true);await load();}
 });
 $('fForm').addEventListener('submit',e=>{e.preventDefault();submit(e.target,'fFormError','fSubmit','adminAjouter','adminModifier','fModal',['Facture ajoutée.','Facture modifiée.'],checkFacture,['idUtilisateur','reference','fournisseur','montant','dateEmission','dateEcheance']);});
 $('mForm').addEventListener('submit',e=>{e.preventDefault();submit(e.target,'mFormError','mSubmit','modeAjouter','modeModifier','mModal',['Mode ajouté.','Mode modifié.'],checkMode,['libelle','description']);});
 // validation à la sortie de chaque champ
 $('fForm').addEventListener('focusout',e=>{const n=e.target.name;if(!n||n==='id')return;const er=checkFacture($('fForm'));setErr($('fForm'),n,er[n]||'');});
 $('mForm').addEventListener('focusout',e=>{const n=e.target.name;if(!n||n==='id')return;const er=checkMode($('mForm'));setErr($('mForm'),n,er[n]||'');});
 document.querySelectorAll('.fac-modal').forEach(m=>m.addEventListener('click',e=>{if(e.target.closest('[data-close]'))close(m.id);}));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'){close('fModal');close('mModal');}});
 load();
})();
