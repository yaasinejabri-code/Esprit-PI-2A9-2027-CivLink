<?php if (!defined('CIVLINK_BACKOFFICE_RENDER')) { http_response_code(403); exit; } ?>
<?php if (!function_exists('admE')) { function admE($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } } ?>
<style>
.civ-adm { color:#243346; } .dark .civ-adm { color:#e5e7eb; }
.civ-adm h1 {font-size:27px;font-weight:750;margin-bottom:8px} .civ-adm h2 {font-size:20px;font-weight:700;margin-bottom:18px}
.civ-adm p.desc {color:#64748b;margin-bottom:20px}.dark .civ-adm p.desc{color:#94a3b8}
.civ-adm .panel {background:white;border:1px solid #e5e7eb;border-radius:16px;padding:24px;margin:20px 0;box-shadow:0 2px 12px #00000007}.dark .civ-adm .panel {background:#1f2937;border-color:#374151}
.civ-adm .grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.civ-adm label {display:block;font-weight:600;font-size:14px}
.civ-adm input,.civ-adm select,.civ-adm textarea{display:block;width:100%;margin-top:6px;padding:11px;border:1px solid #cbd5e1;border-radius:9px;background:white;color:#17253b;font-weight:400}.dark .civ-adm input,.dark .civ-adm select,.dark .civ-adm textarea {background:#111827;color:white;border-color:#475569}
.civ-adm .full{grid-column:1/-1}.civ-adm .button{display:inline-block;background:#f7941d;color:#fff;border:0;border-radius:9px;padding:11px 18px;font-weight:700;cursor:pointer;margin-top:17px}.civ-adm .secondary{background:#eef1f5;color:#263449;margin-left:9px}.civ-adm .danger{background:#b91c1c}.civ-adm .tiny{padding:7px 10px;margin:0 5px 0 0;font-size:12px}
.civ-adm .scroll{overflow-x:auto}.civ-adm table {width:100%;border-collapse:collapse;min-width:720px}.civ-adm th,.civ-adm td{padding:12px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:middle}.dark .civ-adm th,.dark .civ-adm td{border-color:#374151}.civ-adm th{font-size:13px;color:#64748b}.civ-adm form.inline{display:inline-block}.civ-adm .notice{padding:12px 16px;border-radius:8px;background:#fff7ed;color:#9a3412;margin:14px 0}.civ-adm .good{background:#ecfdf5;color:#047857}.civ-adm .muted{color:#64748b;font-size:13px}.civ-adm .location{position:relative}.civ-adm #admSuggestions{position:absolute;z-index:100;left:0;right:0;max-height:220px;overflow:auto;background:white;box-shadow:0 8px 22px #0002;border-radius:9px}.dark .civ-adm #admSuggestions{background:#1f2937}.civ-adm #admSuggestions button{display:block;width:100%;text-align:left;padding:9px;border:0;background:transparent;color:inherit;cursor:pointer}.civ-adm #admSuggestions button:hover{background:#fff1e6;color:#17253b}
@media(max-width:650px){.civ-adm .grid{grid-template-columns:1fr}.civ-adm .panel{padding:16px}}
</style>
<div class="civ-adm">
<h1>Gestion des administrations et services</h1>
<p class="desc">Ajoutez et gérez les lieux qui apparaissent sur la carte du Front Office et les services rattachés à chaque administration.</p>
<?php if ($error): ?><div class="notice" role="alert"><?=admE($error)?></div><?php endif; ?>
<?php if (isset($_GET['ok'])): ?><div class="notice good">Modification enregistrée.</div><?php endif; ?>
<section class="panel"><h2><?= $edit ? 'Modifier une administration' : 'Ajouter une administration' ?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?=admE(authToken())?>"><input type="hidden" name="action" value="<?=$edit?'admin_edit':'admin_add'?>"><input type="hidden" name="id" value="<?=(int)($edit['idAdministration']??0)?>">
<div class="grid">
<label>Nom *<input name="nom" required maxlength="160" value="<?=admE($edit['nom']??'')?>" placeholder="Municipalité de Tunis"></label>
<label>Type *<select name="typeAdministration" required><?php foreach($types as $key=>$t): ?><option value="<?=admE($key)?>" <?=($edit['typeAdministration']??'')===$key?'selected':''?>><?=admE($t['nom'])?></option><?php endforeach; ?></select></label>
<div class="location"><label>Adresse *<input id="admAdresse" name="adresse" required maxlength="220" autocomplete="off" value="<?=admE($edit['adresse']??'')?>" placeholder="Recherchez une adresse en Tunisie"></label><div id="admSuggestions"></div></div>
<label>Horaires<input name="horaires" maxlength="120" value="<?=admE($edit['horaires']??'')?>" placeholder="Lun–Ven, 8h–16h"></label>
<label>Téléphone<input name="telephone" maxlength="40" value="<?=admE($edit['telephone']??'')?>" placeholder="+216 ..."></label>
<label>Latitude *<input type="number" step="any" min="-90" max="90" id="admLat" name="latitude" required value="<?=admE($edit['latitude']??'')?>" placeholder="36.8065"></label>
<label>Longitude *<input type="number" step="any" min="-180" max="180" id="admLng" name="longitude" required value="<?=admE($edit['longitude']??'')?>" placeholder="10.1815"></label>
<label>Statut<select name="actif"><option value="1" <?=($edit['actif']??1)?'selected':''?>>Actif</option><option value="0" <?=!($edit['actif']??1)?'selected':''?>>Désactivé</option></select></label>
</div><button class="button" type="submit"><?=$edit?'Enregistrer les modifications':'Ajouter l’administration'?></button><?php if($edit):?><a class="button secondary" href="index.php?page=administrations">Annuler</a><?php endif; ?>
</form></section>
<section class="panel"><h2>Administrations (<?=count($admins)?>)</h2><div class="scroll"><table><thead><tr><th>Administration</th><th>Type</th><th>Adresse</th><th>État</th><th>Actions</th></tr></thead><tbody><?php foreach($admins as $a): ?><tr><td><strong><?=admE($a['nom'])?></strong></td><td><?=admE($types[$a['typeAdministration']]['nom']??$a['typeAdministration'])?></td><td><?=admE($a['adresse'])?></td><td><?= (int)$a['actif']?'Active':'Désactivée'?></td><td><a class="button secondary tiny" href="index.php?page=administrations&amp;edit=<?=(int)$a['idAdministration']?>">Modifier</a><form class="inline" method="post" onsubmit="return confirm('Supprimer cette administration et ses services ?')"><input type="hidden" name="csrf" value="<?=admE(authToken())?>"><input type="hidden" name="action" value="admin_delete"><input type="hidden" name="id" value="<?=(int)$a['idAdministration']?>"><button class="button danger tiny" type="submit">Supprimer</button></form></td></tr><?php endforeach;?></tbody></table></div></section>
<section class="panel" id="services"><h2><?=$editService?'Modifier un service':'Ajouter un service'?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?=admE(authToken())?>"><input type="hidden" name="action" value="<?=$editService?'service_edit':'service_add'?>"><input type="hidden" name="id" value="<?=(int)($editService['idService']??0)?>"><div class="grid">
<label>Administration *<select name="idAdministration" required><option value="">Choisir une administration</option><?php foreach($admins as $a):?><option value="<?=(int)$a['idAdministration']?>" <?=($editService['idAdministration']??'')==$a['idAdministration']?'selected':''?>><?=admE($a['nom'])?></option><?php endforeach;?></select></label>
<label>Nom du service *<input name="nomService" required maxlength="120" value="<?=admE($editService['nom']??'')?>" placeholder="État civil"></label>
<label class="full">Description<textarea name="description" maxlength="2000" rows="2"><?=admE($editService['description']??'')?></textarea></label>
<label>Statut<select name="actif"><option value="1" <?=($editService['actif']??1)?'selected':''?>>Actif</option><option value="0" <?=!($editService['actif']??1)?'selected':''?>>Désactivé</option></select></label>
</div><button class="button" type="submit"><?=$editService?'Enregistrer les modifications':'Ajouter le service'?></button><?php if($editService):?><a class="button secondary" href="index.php?page=administrations#services">Annuler</a><?php endif;?></form></section>
<section class="panel"><h2>Services (<?=count($services)?>)</h2><div class="scroll"><table><thead><tr><th>Service</th><th>Administration</th><th>Description</th><th>État</th><th>Actions</th></tr></thead><tbody><?php foreach($services as $s):?><tr><td><strong><?=admE($s['nom'])?></strong></td><td><?=admE($s['administrationNom'])?></td><td><?=admE($s['description'])?></td><td><?= (int)$s['actif']?'Actif':'Désactivé'?></td><td><a class="button secondary tiny" href="index.php?page=administrations&amp;editservice=<?=(int)$s['idService']?>#services">Modifier</a><form class="inline" method="post" onsubmit="return confirm('Supprimer ce service ?')"><input type="hidden" name="csrf" value="<?=admE(authToken())?>"><input type="hidden" name="action" value="service_delete"><input type="hidden" name="id" value="<?=(int)$s['idService']?>"><button class="button danger tiny">Supprimer</button></form></td></tr><?php endforeach;?></tbody></table></div></section>
</div>
<script>
(function(){
const inp=document.getElementById('admAdresse'),box=document.getElementById('admSuggestions');
const lat=document.getElementById('admLat'),lng=document.getElementById('admLng');
if(!inp||!box)return;
let timer,controller,sequence=0;
function close(){box.replaceChildren();}
inp.addEventListener('input',()=>{
 clearTimeout(timer); if(controller)controller.abort(); sequence++; close();
 const q=inp.value.trim(),id=sequence;
 if(q.length<3)return;
 timer=setTimeout(async()=>{
  controller=new AbortController();
  try{
   const res=await fetch('https://photon.komoot.io/api/?q='+encodeURIComponent(q)+'&countrycode=TN&limit=6&lang=fr',{signal:controller.signal});
   if(!res.ok)throw Error('fetch'); const data=await res.json(); if(id!==sequence)return;
   (data.features||[]).forEach(f=>{
    const p=f.properties||{},coords=f.geometry?.coordinates;
    if(!coords||(p.countrycode&&p.countrycode.toUpperCase()!=='TN'))return;
    const label=[...new Set([p.name,p.street,p.city,p.state].filter(Boolean))].join(', ');if(!label)return;
    const b=document.createElement('button');b.type='button';b.textContent='📍 '+label;
    b.addEventListener('click',()=>{inp.value=label;lat.value=coords[1];lng.value=coords[0];sequence++;close();});box.appendChild(b);
   });
  }catch(e){if(e.name!=='AbortError')close();}
 },400);
});
document.addEventListener('click',e=>{if(!inp.parentElement.parentElement.contains(e.target))close();});
})();
</script>
