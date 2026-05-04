<?php
$host='localhost';$dbname='rh_altutex';$user='root';$pass='';
try{$pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
catch(PDOException $e){die('<p style="color:red;padding:20px;">Connexion impossible : '.$e->getMessage().'</p>');}
if(!isset($_SESSION['matricule'])){header('Location: index.php?action=login');exit;}
$matricule=$_SESSION['matricule'];$annee=date('Y');

$stmtEmp=$pdo->prepare("SELECT *,TIMESTAMPDIFF(YEAR,date_entree,CURDATE()) AS anciennete_calc FROM information_employe WHERE matricule=:mat LIMIT 1");
$stmtEmp->execute([':mat'=>$matricule]);$emp=$stmtEmp->fetch();
if(!$emp)die('<p style="color:red;padding:20px;">Employé introuvable.</p>');
$nomComplet=htmlspecialchars(trim($emp['prenom'].' '.$emp['nom']));
$initiales=strtoupper(substr($emp['prenom'],0,1).substr($emp['nom'],0,1));

$stmtAbsKpi=$pdo->prepare("SELECT COUNT(*) AS total_absences,SUM(type_declaration='absence') AS nb_absences,SUM(type_declaration='retard') AS nb_retards,SUM(MONTH(date_abs)=MONTH(CURDATE()) AND YEAR(date_abs)=YEAR(CURDATE())) AS ce_mois,MIN(date_abs) AS premiere,MAX(date_abs) AS derniere FROM absences WHERE user_id=:mat AND YEAR(date_abs)=:annee");
$stmtAbsKpi->execute([':mat'=>$matricule,':annee'=>$annee]);$kpiAbs=$stmtAbsKpi->fetch();
$joursOuvres=261;$tauxAbsenteisme=($kpiAbs['nb_absences']??0)>0?round($kpiAbs['nb_absences']/$joursOuvres*100,1):0;

$stmtListeAbs=$pdo->prepare("SELECT date_abs,type_declaration,heure_seance,motif,remarque FROM absences WHERE user_id=:mat ORDER BY date_abs DESC LIMIT 8");
$stmtListeAbs->execute([':mat'=>$matricule]);$listeAbsences=$stmtListeAbs->fetchAll();

$CONGE_MAX_DEMANDES=2;$CONGE_MAX_JOURS=14;$droitAnnuel=30;
$stmtCongeKpi=$pdo->prepare("SELECT COUNT(*) AS total_demandes,SUM(statut='validé') AS validees,SUM(statut='refusé') AS refusees,SUM(statut IS NULL OR statut NOT IN('validé','refusé')) AS en_attente,SUM(CASE WHEN statut='validé' THEN DATEDIFF(date_fin,date_debut)+1 ELSE 0 END) AS jours_pris FROM demandes_conge WHERE matricule=:mat AND YEAR(date_debut)=:annee");
$stmtCongeKpi->execute([':mat'=>$matricule,':annee'=>$annee]);$kpiConge=$stmtCongeKpi->fetch();
$nbDemandesAnnee=(int)($kpiConge['total_demandes']??0);
$demandesRestantes=max(0,$CONGE_MAX_DEMANDES-$nbDemandesAnnee);
$soldeRestant=$droitAnnuel-(int)($kpiConge['jours_pris']??0);
$hasFormations=!empty($pdo->query("SHOW TABLES LIKE 'formations'")->fetchAll());
$hasParticip=!empty($pdo->query("SHOW TABLES LIKE 'participations_formations'")->fetchAll());
$kpiForm=['total'=>0,'terminees'=>0,'en_cours'=>0,'planifiees'=>0,'heures'=>0];
if($hasFormations&&$hasParticip){$r=$pdo->prepare("SELECT COUNT(*) AS total,SUM(pf.statut_participation='terminé') AS terminees,SUM(pf.statut_participation='en cours') AS en_cours,SUM(pf.statut_participation='planifié') AS planifiees,COALESCE(SUM(f.duree_heures),0) AS heures FROM participations_formations pf LEFT JOIN formations f ON pf.id_formation=f.id_formation WHERE pf.matricule=:mat");$r->execute([':mat'=>$matricule]);$row=$r->fetch();$kpiForm=['total'=>(int)$row['total'],'terminees'=>(int)$row['terminees'],'en_cours'=>(int)$row['en_cours'],'planifiees'=>(int)$row['planifiees'],'heures'=>(int)$row['heures']];}

$stmtListeConge=$pdo->prepare("SELECT date_debut,date_fin,DATEDIFF(date_fin,date_debut)+1 AS nb_jours,cause,statut,date_demande,date_validation FROM demandes_conge WHERE matricule=:mat ORDER BY date_demande DESC LIMIT 8");
$stmtListeConge->execute([':mat'=>$matricule]);$listeConges=$stmtListeConge->fetchAll();

function statutBadge(string $s):string{
  $map=['validé'=>['bg:#d1fae5;color:#065f46','✓ '],'refusé'=>['bg:#fee2e2;color:#991b1b','✕ '],
        'en attente'=>['bg:#fef3c7;color:#92400e','◎ '],'terminé'=>['bg:#d1fae5;color:#065f46','✓ '],
        'en cours'=>['bg:#dbeafe;color:#1e40af','▶ '],'planifié'=>['bg:#ede9fe;color:#5b21b6','◆ '],
        'actif'=>['bg:#d1fae5;color:#065f46','● ']];
  $c=$map[strtolower(trim($s))]??['bg:#f3f4f6;color:#374151',''];
  return '<span style="'.str_replace('bg:','background:',$c[0]).';font-size:10.5px;padding:3px 10px;border-radius:20px;font-weight:600;white-space:nowrap;">'.$c[1].htmlspecialchars($s).'</span>';
}
function fdate(?string $d):string{return $d?date('d/m/Y',strtotime($d)):'—';}
function typeBadge(string $t):string{
  return strtolower($t)==='retard'
    ?'<span style="background:#fef3c7;color:#92400e;font-size:10.5px;padding:3px 10px;border-radius:20px;font-weight:600;">⏰ Retard</span>'
    :'<span style="background:#fee2e2;color:#991b1b;font-size:10.5px;padding:3px 10px;border-radius:20px;font-weight:600;">✕ Absence</span>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Altutex — Espace Employé</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0d1b4b;--navy2:#172268;
  --v:#4f46e5;--v2:#7c3aed;
  --tl:#0d9488;--ro:#e11d48;--am:#d97706;--li:#16a34a;--sk:#0284c7;--fu:#a21caf;
  --bg:#eef0f8;--wh:#fff;--bd:rgba(79,70,229,.1);--tx:#0f172a;--mu:#64748b;
  --r:16px;--sb:244px;--sh:0 2px 16px rgba(79,70,229,.09);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--tx);display:flex;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{width:var(--sb);background:linear-gradient(175deg,var(--navy) 0%,var(--navy2) 100%);position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:100;border-right:1px solid rgba(255,255,255,.05)}
.sb-top{padding:24px 20px 18px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:13px}
.sb-ico{width:40px;height:40px;background:linear-gradient(135deg,var(--v),var(--v2));border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 14px rgba(79,70,229,.45)}
.sb-brand{font-size:15px;font-weight:700;color:#fff;letter-spacing:3.5px}
.sb-nav{padding:16px 12px;flex:1;overflow-y:auto}
.sb-sec{font-size:8.5px;color:rgba(255,255,255,.22);letter-spacing:2.5px;padding:0 10px;margin:20px 0 7px;text-transform:uppercase;font-weight:700}
.ni{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:11px;color:rgba(255,255,255,.42);font-size:12.5px;cursor:pointer;margin-bottom:3px;text-decoration:none;transition:all .18s;font-weight:500;border-left:3px solid transparent}
.ni:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.8)}
.ni.on{background:linear-gradient(90deg,rgba(79,70,229,.3),rgba(79,70,229,.07));color:#fff;border-left-color:var(--v)}
.ni svg{width:16px;height:16px;flex-shrink:0}
.sb-bot{padding:12px;border-top:1px solid rgba(255,255,255,.07)}
.ni-out{color:#fca5a5!important}
.ni-out:hover{background:rgba(239,68,68,.14)!important;color:#fecaca!important}

/* ── MAIN ── */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-height:100vh}

/* ── TOPBAR ── */
.topbar{background:rgba(255,255,255,.88);backdrop-filter:blur(16px);border-bottom:1px solid var(--bd);height:60px;display:flex;align-items:center;justify-content:space-between;padding:0 34px;position:sticky;top:0;z-index:50}
.tb-bc{font-size:10px;color:var(--mu);letter-spacing:.5px;font-weight:500;margin-bottom:2px}
.tb-t{font-size:14.5px;font-weight:700;background:linear-gradient(90deg,var(--v),var(--v2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.tb-r{display:flex;align-items:center;gap:10px}
.tb-date{font-size:11px;color:var(--mu);background:#f1f2f8;border:1px solid var(--bd);border-radius:9px;padding:5px 13px;font-family:'DM Mono',monospace;font-weight:500}
.tb-bell{width:38px;height:38px;border-radius:11px;background:#f1f2f8;border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;text-decoration:none;position:relative;transition:all .18s}
.tb-bell:hover{background:#fff;box-shadow:var(--sh)}
.tb-dot{width:8px;height:8px;background:var(--ro);border-radius:50%;position:absolute;top:6px;right:6px;border:2px solid #fff}
.tb-ava{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--v),var(--v2));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;box-shadow:0 2px 10px rgba(79,70,229,.35)}

/* ── CONTENT ── */
.content{padding:28px 34px;flex:1}

/* ── HERO ── */
.hero{
  background:linear-gradient(130deg,#0d1b4b 0%,#1e3a8a 45%,#4f46e5 75%,#7c3aed 100%);
  border-radius:22px;padding:0;margin-bottom:28px;
  display:grid;grid-template-columns:1fr auto;
  position:relative;overflow:hidden;
  box-shadow:0 12px 40px rgba(79,70,229,.3);
  min-height:140px;
}
.hero-left{padding:28px 32px;display:flex;align-items:center;gap:22px;z-index:1}
.hero-deco{position:absolute;right:-20px;top:-40px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.hero-deco2{position:absolute;left:30%;bottom:-60px;width:180px;height:180px;border-radius:50%;background:rgba(124,58,237,.18);pointer-events:none}
.hero-deco3{position:absolute;right:220px;top:10px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.06);pointer-events:none}
.emp-ava{width:62px;height:62px;border-radius:50%;background:rgba(255,255,255,.13);border:2.5px solid rgba(255,255,255,.28);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.emp-dot{width:13px;height:13px;background:#4ade80;border-radius:50%;border:2.5px solid #1e3a8a;box-shadow:0 0 8px rgba(74,222,128,.7);margin-top:-16px;margin-left:46px}
.emp-name{font-size:20px;font-weight:700;color:#fff;margin-bottom:6px;line-height:1.2}
.emp-meta{font-size:11.5px;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.emp-bdg{background:rgba(255,255,255,.14);color:#fff;font-size:10px;border-radius:7px;padding:3px 10px;font-weight:600;border:1px solid rgba(255,255,255,.2);letter-spacing:.3px}

/* Hero KPI rail */
.hero-kpis{display:flex;align-items:stretch;border-left:1px solid rgba(255,255,255,.1);z-index:1}
.hk{padding:28px 26px;text-align:center;border-left:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;transition:background .18s;cursor:default}
.hk:hover{background:rgba(255,255,255,.05)}
.hk-v{font-size:26px;font-weight:700;color:#fff;line-height:1}
.hk-l{font-size:9px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:1px;font-weight:600;white-space:nowrap}
.hk-u{font-size:11px;color:rgba(255,255,255,.35);font-weight:400;margin-top:1px}

/* ── ALERT ── */
.al{border-radius:13px;padding:13px 18px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:12.5px;font-weight:500}
.al-ico{font-size:17px;flex-shrink:0}
.al-warn{background:linear-gradient(90deg,#fffbeb,#fef9ed);border:1px solid #fbbf24;color:#78350f}
.al-err{background:linear-gradient(90deg,#fff5f5,#fff1f1);border:1px solid #fca5a5;color:#7f1d1d}
.al-ok{background:linear-gradient(90deg,#f0fdf4,#ecfdf5);border:1px solid #86efac;color:#14532d}

/* ── SECTION ── */
.sh{display:flex;align-items:center;justify-content:space-between;margin-bottom:15px}
.sl{font-size:10.5px;font-weight:700;color:var(--mu);letter-spacing:1.5px;text-transform:uppercase;display:flex;align-items:center;gap:9px}
.sl-bar{width:3px;height:14px;border-radius:2px;background:linear-gradient(to bottom,var(--v),var(--v2));display:inline-block}
.sa{font-size:11px;color:var(--v);text-decoration:none;font-weight:600;padding:4px 13px;border:1px solid rgba(79,70,229,.2);border-radius:9px;transition:all .18s}
.sa:hover{background:var(--v);color:#fff}

/* ── 2-COL LAYOUT ── */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-bottom:28px}
.one-col{margin-bottom:28px}

/* ── MODULE CARDS ── */
.mg{display:grid;grid-template-columns:repeat(5,1fr);gap:13px;margin-bottom:30px}
@media(max-width:2200px){.mg{grid-template-columns:repeat(3,1fr)}}
.mc{background:var(--wh);border:2px solid var(--bd);border-radius:var(--r);padding:30px 20px;cursor:pointer;text-decoration:none;transition:all .22s cubic-bezier(.36,2,.64,1);display:flex;flex-direction:column;gap:11px;position:relative;overflow:hidden}
.mc::after{content:'';position:absolute;top:0;left:0;right:0;height:3.5px;border-radius:var(--r) var(--r) 0 0}
.mc.cr::after{background:linear-gradient(90deg,#e11d48,#fb7185)}
.mc.cg::after{background:linear-gradient(90deg,#16a34a,#4ade80)}
.mc.cb::after{background:linear-gradient(90deg,#0284c7,#38bdf8)}
.mc.ca::after{background:linear-gradient(90deg,#d97706,#fbbf24)}
.mc.cp::after{background:linear-gradient(90deg,#7c3aed,#a78bfa)}
.mc:hover{transform:translateY(-5px);box-shadow:0 16px 36px rgba(79,70,229,.13);border-color:rgba(79,70,229,.25)}
.mc-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center}
.mc-ico svg{width:19px;height:19px}
.ico-r{background:#fff1f2}.ico-r svg{stroke:#e11d48}
.ico-g{background:#f0fdf4}.ico-g svg{stroke:#16a34a}
.ico-b{background:#eff6ff}.ico-b svg{stroke:#0284c7}
.ico-a{background:#fffbeb}.ico-a svg{stroke:#d97706}
.ico-p{background:#faf5ff}.ico-p svg{stroke:#7c3aed}
.mc-t{font-size:12.5px;font-weight:600;color:var(--tx)}
.mc-d{font-size:11px;color:var(--mu);line-height:1.5}
.mc-lnk{font-size:11px;font-weight:700}
.cl-r{color:#e11d48}.cl-g{color:#16a34a}.cl-b{color:#0284c7}.cl-a{color:#d97706}.cl-p{color:#7c3aed}
.mc-disabled{opacity:.55;pointer-events:none;filter:grayscale(.4)}

/* ── KPI SECTION PAIR ── */
.kpi-section{background:var(--wh);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.ks-head{padding:16px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(90deg,#fafafe,var(--wh))}
.ks-title{font-size:13px;font-weight:700;color:var(--tx);display:flex;align-items:center;gap:9px}
.ks-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px}
.ks-badge{font-size:10px;font-weight:700;color:var(--mu);font-family:'DM Mono',monospace;background:#f1f2f8;padding:3px 9px;border-radius:7px}
.ks-link{font-size:11px;color:var(--v);text-decoration:none;font-weight:600;padding:3px 11px;border:1px solid rgba(79,70,229,.2);border-radius:8px;transition:all .18s}
.ks-link:hover{background:var(--v);color:#fff}

/* KPI chips */
.kchips{display:grid;grid-template-columns:repeat(2,1fr);gap:0}
.kchip{padding:16px 20px;border-bottom:1px solid rgba(0,0,0,.04);border-right:1px solid rgba(0,0,0,.04);display:flex;align-items:center;gap:13px;transition:background .15s}
.kchip:hover{background:#fafafe}
.kchip:nth-child(2n){border-right:none}
.kchip:nth-last-child(-n+2){border-bottom:none}
.kch-dot{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.kch-v{font-size:20px;font-weight:700;line-height:1}
.kch-l{font-size:10px;color:var(--mu);font-weight:500;margin-top:1px}
.kch-s{font-size:9.5px;color:var(--mu);margin-top:2px;font-weight:400}
.dot-v{background:#ede9fe;color:#5b21b6}
.dot-r{background:#fee2e2;color:#991b1b}
.dot-a{background:#fef3c7;color:#92400e}
.dot-g{background:#d1fae5;color:#065f46}
.dot-b{background:#dbeafe;color:#1e40af}
.dot-t{background:#ccfbf1;color:#065f46}
.dot-fu{background:#fae8ff;color:#7e22ce}

/* ── TABLE ── */
.tw{overflow:hidden}
.tw-hd{padding:0 20px;border-bottom:1px solid rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between}
.tw-label{font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.8px;padding:13px 0}
.tw-chip{font-size:9.5px;font-family:'DM Mono',monospace;color:var(--mu);background:#f1f2f8;padding:2px 8px;border-radius:6px}
table{width:100%;border-collapse:collapse}
th{font-size:9.5px;color:var(--mu);font-weight:700;text-align:left;padding:9px 20px;border-bottom:1px solid rgba(0,0,0,.05);letter-spacing:.8px;text-transform:uppercase;background:#fafafe}
td{font-size:12px;color:var(--tx);padding:10px 20px;border-bottom:1px solid rgba(0,0,0,.03);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8f7ff}
.tm{color:var(--mu);font-size:11px}
.ec td{text-align:center;color:var(--mu);padding:28px!important;font-size:12px}

/* ── INFOS PERSO ── */
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:0}
.igr{padding:12px 20px;border-bottom:1px solid rgba(0,0,0,.04);display:flex;flex-direction:column;gap:3px}
.igr:nth-child(odd){border-right:1px solid rgba(0,0,0,.04)}
.igl{font-size:9.5px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.6px}
.igv{font-size:13px;font-weight:600;color:var(--tx)}

/* ── ANIMATIONS ── */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.hero{animation:up .45s ease both}
.mg .mc:nth-child(1){animation:up .4s .08s both}
.mg .mc:nth-child(2){animation:up .4s .14s both}
.mg .mc:nth-child(3){animation:up .4s .19s both}
.mg .mc:nth-child(4){animation:up .4s .24s both}
.mg .mc:nth-child(5){animation:up .4s .29s both}
.two-col{animation:up .4s .2s both}

/* Harmonisation avec les dashboards RH */
:root {
  --navy:#0b1f3a;
  --navy2:#122444;
  --v:#1e6ff1;
  --v2:#0ea5a0;
  --tl:#0ea5a0;
  --ro:#e5405e;
  --am:#f59e0b;
  --li:#16a34a;
  --sk:#1e6ff1;
  --fu:#7c3aed;
  --bg:#f1f4f9;
  --wh:#ffffff;
  --bd:#e4e8f0;
  --tx:#0b1f3a;
  --mu:#64748b;
  --r:16px;
  --sb:260px;
  --sh:0 8px 20px rgba(15,31,58,.045);
}
body {
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--tx);
}
.sidebar {
  background:var(--navy);
  border-right:0;
}
.sb-top {
  padding:28px 22px 20px;
  border-bottom:1px solid rgba(255,255,255,.07);
}
.sb-ico {
  width:8px;
  height:8px;
  border-radius:50%;
  background:var(--v);
  box-shadow:0 0 8px var(--v);
}
.sb-ico svg { display:none; }
.sb-brand {
  font-family:'DM Serif Display',serif;
  font-size:1.6rem;
  font-weight:400;
  letter-spacing:.5px;
}
.sb-sec {
  color:rgba(255,255,255,.25);
  font-size:.65rem;
  letter-spacing:2px;
}
.ni {
  color:rgba(255,255,255,.55);
  border-left:0;
  border-radius:12px;
  padding:11px 12px;
  font-size:.875rem;
  font-weight:500;
}
.ni:hover { background:rgba(255,255,255,.06); color:#fff; }
.ni.on {
  background:rgba(30,111,241,.18);
  border-left:0;
  color:#fff;
}
.topbar {
  height:72px;
  background:rgba(241,244,249,.94);
  border-bottom:0;
  box-shadow:none;
  padding:0 38px;
}
.tb-bc {
  color:var(--mu);
  font-size:.76rem;
}
.tb-t {
  background:none;
  -webkit-text-fill-color:initial;
  color:var(--tx);
  font-size:1.15rem;
  font-weight:700;
}
.tb-date,
.tb-bell {
  background:#fff;
  border:1px solid var(--bd);
  border-radius:30px;
  box-shadow:none;
}
.tb-ava {
  background:var(--v);
  box-shadow:none;
}
.content { padding:22px 38px 48px; }
.hero {
  background:linear-gradient(130deg,#0b1f3a 0%,#12345f 52%,#1e6ff1 100%);
  border-radius:18px;
  box-shadow:0 14px 34px rgba(15,31,58,.18);
}
.hero-kpis { border-left:1px solid rgba(255,255,255,.12); }
.hk { padding:28px 24px; }
.al {
  border-radius:16px;
  box-shadow:var(--sh);
}
.sl {
  color:var(--mu);
  font-size:.65rem;
  letter-spacing:1.8px;
}
.sl-bar { background:var(--v); }
.mg {
  gap:18px;
  grid-auto-rows:1fr;
}
.mg .mc { order:1; }
.mg .mc.cb {
  order:2;
  grid-column:span 2;
}
.mc {
  min-height:176px;
  height:100%;
  padding:18px 20px;
  border:1px solid var(--bd);
  border-radius:18px;
  background:#fff;
  box-shadow:var(--sh);
  gap:10px;
}
.mc:hover {
  transform:translateY(-3px);
  border-color:rgba(148,163,184,.95);
  box-shadow:0 16px 34px rgba(15,31,58,.10);
}
.mc::after {
  top:0;
  left:0;
  bottom:0;
  right:auto;
  width:4px;
  height:auto;
  border-radius:18px 0 0 18px;
}
.mc.cr { background:linear-gradient(180deg,#fff 0%,#fff3f5 100%); }
.mc.cg { background:linear-gradient(180deg,#fff 0%,#f0fbf5 100%); }
.mc.cb { background:linear-gradient(180deg,#fff 0%,#f3f8ff 100%); }
.mc.ca { background:linear-gradient(180deg,#fff 0%,#fff8ed 100%); }
.mc.cp { background:linear-gradient(180deg,#fff 0%,#f8f4ff 100%); }
.mc-ico {
  width:48px;
  height:48px;
  border-radius:14px;
}
.mc-t {
  font-size:1rem;
  font-weight:700;
  color:var(--tx);
}
.mc-d {
  font-size:.875rem;
  line-height:1.55;
  color:#56657a;
  min-height:42px;
}
.mc-lnk {
  align-self:flex-start;
  border-radius:30px;
  padding:7px 14px;
  font-size:.68rem;
  letter-spacing:.7px;
  text-transform:uppercase;
}
.cl-r { background:#ffe5ea;color:#be123c; }
.cl-g { background:#e5f8ed;color:#15803d; }
.cl-b { background:#eaf3ff;color:#2563eb; }
.cl-a { background:#fff1d9;color:#b36a08; }
.cl-p { background:#f0e9ff;color:#7c3aed; }
.kpi-section {
  border:1px solid var(--bd);
  border-radius:18px;
  box-shadow:var(--sh);
}
.ks-head {
  background:#fff;
  padding:17px 20px;
}
.ks-title {
  font-size:1rem;
  font-weight:700;
}
.ks-icon {
  width:34px;
  height:34px;
  border-radius:10px;
}
.ks-link,
.sa {
  border-radius:30px;
  border-color:rgba(30,111,241,.16);
  color:var(--v);
}
.ks-link { display:none; }
.ks-link:hover,
.sa:hover {
  background:var(--v);
}
.kchip { padding:17px 20px; }
.kchip:hover { background:#f8fbff; }
.kch-dot {
  width:38px;
  height:38px;
  border-radius:12px;
}
.kch-v { font-family:'DM Sans',sans-serif; }
th {
  background:#f8fafc;
  color:var(--mu);
}
tr:hover td { background:#f8fbff; }

@media(max-width:900px){
  .main{margin-left:0}
  .sidebar{display:none}
  .topbar{padding:0 20px}
  .content{padding:20px}
  .mg .mc.cb{grid-column:span 1}
}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-top">
    <div class="sb-ico">
      <svg viewBox="0 0 18 18" fill="none" width="18" height="18">
        <rect x="2" y="2" width="6" height="6" rx="1.5" fill="white"/>
        <rect x="10" y="2" width="6" height="6" rx="1.5" fill="white" fill-opacity=".7"/>
        <rect x="2" y="10" width="6" height="6" rx="1.5" fill="white" fill-opacity=".7"/>
        <rect x="10" y="10" width="6" height="6" rx="1.5" fill="white" fill-opacity=".35"/>
      </svg>
    </div>
    <span class="sb-brand">ALTUTEX</span>
  </div>
  <div class="sb-nav">
    <div class="sb-sec">Principal</div>
    <a href="index.php?action=dashboard" class="ni on">
      <svg fill="none" viewBox="0 0 15 15"><path d="M1.5 6.5L7.5 1.5L13.5 6.5V13H10V9.5H5V13H1.5V6.5Z" stroke="white" stroke-width="1.3" stroke-linejoin="round"/></svg>Tableau de bord</a>
    <a href="index.php?action=mon_profil" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><circle cx="7.5" cy="5.5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1.5 13.5C1.5 11.015 4.186 9 7.5 9C10.814 9 13.5 11.015 13.5 13.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Mon profil</a>
    <a href="index.php?action=notifications" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><path d="M7.5 1.5C5.015 1.5 3 3.515 3 6V9L1.5 10.5V11.5H13.5V10.5L12 9V6C12 3.515 9.985 1.5 7.5 1.5Z" stroke="currentColor" stroke-width="1.3"/><path d="M6 12C6 12.83 6.67 13.5 7.5 13.5C8.33 13.5 9 12.83 9 12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Notifications</a>
    <div class="sb-sec">Gestion RH</div>
    <a href="index.php?action=demande_conge" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><rect x="2" y="3.5" width="11" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 3.5V2.5M10 3.5V2.5M2 6.5H13" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M5 9.5L6.5 11L10 8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Demande de congé</a>
    <a href="index.php?action=declaration_absence" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="5.5" stroke="currentColor" stroke-width="1.3"/><path d="M7.5 4.5V7.5L9.5 9.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Déclaration absence</a>
    <a href="index.php?action=mes_documents" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><rect x="3" y="1.5" width="9" height="12" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 5H10M5 7.5H10M5 10H7.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Mes documents</a>
    <div class="sb-sec">Formation</div>
    <a href="index.php?action=planning" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><path d="M7.5 2L13.5 5.5L7.5 9L1.5 5.5L7.5 2Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M4 7.5V11C4 11 5.5 12.5 7.5 12.5C9.5 12.5 11 11 11 11V7.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Mes formations</a>
    <a href="index.php?action=enquetes" class="ni">
      <svg fill="none" viewBox="0 0 15 15"><rect x="2" y="2" width="11" height="11" rx="1.5" stroke="currentColor" stroke-width="1.3"/><circle cx="5" cy="6" r="1" fill="currentColor"/><circle cx="5" cy="9.5" r="1" fill="currentColor"/><path d="M8 6H12M8 9.5H12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Enquêtes</a>
  </div>
  <div class="sb-bot">
    <a href="index.php?action=logout" class="ni ni-out">
      <svg fill="none" viewBox="0 0 15 15"><path d="M6 2.5H3C2.72 2.5 2.5 2.72 2.5 3V12C2.5 12.28 2.72 12.5 3 12.5H6M10 4.5L12.5 7.5L10 10.5M12.5 7.5H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Déconnexion</a>
  </div>
</div>

<div class="main">
  <div class="topbar">
    <div>
      <div class="tb-bc">Altutex · Espace Employé</div>
      <div class="tb-t">Tableau de bord</div>
    </div>
    <div class="tb-r">
      <span class="tb-date"><?= date('d/m/Y') ?></span>
      <a href="index.php?action=notifications" class="tb-bell">
        <svg width="16" height="16" fill="none" viewBox="0 0 15 15"><path d="M7.5 1.5C5.015 1.5 3 3.515 3 6V9L1.5 10.5V11.5H13.5V10.5L12 9V6C12 3.515 9.985 1.5 7.5 1.5Z" stroke="#64748b" stroke-width="1.3"/><path d="M6 12C6 12.83 6.67 13.5 7.5 13.5C8.33 13.5 9 12.83 9 12" stroke="#64748b" stroke-width="1.3" stroke-linecap="round"/></svg>
        <div class="tb-dot"></div>
      </a>
      <div class="tb-ava"><?= $initiales ?></div>
    </div>
  </div>

  <div class="content">

    <!-- HERO -->
    <div class="hero">
      <div class="hero-deco"></div><div class="hero-deco2"></div><div class="hero-deco3"></div>
      <div class="hero-left">
        <div>
          <div class="emp-ava">
            <svg width="32" height="32" viewBox="0 0 30 30" fill="none">
              <circle cx="15" cy="11" r="6" fill="rgba(255,255,255,.75)"/>
              <path d="M4 27C4 21.477 9.029 17 15 17C20.971 17 26 21.477 26 27" stroke="rgba(255,255,255,.75)" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="emp-dot"></div>
        </div>
        <div>
          <div class="emp-name"><?= $nomComplet ?></div>
          <div class="emp-meta">
            <span class="emp-bdg"><?= htmlspecialchars($emp['type_contrat']??'CDI') ?></span>
            <?= htmlspecialchars($emp['fonction']??'') ?>
            &nbsp;·&nbsp;<?= htmlspecialchars($emp['departement']??'') ?>
            &nbsp;·&nbsp;<span style="color:rgba(255,255,255,.75);font-family:'DM Mono',monospace">Mat. <?= htmlspecialchars($matricule) ?></span>
          </div>
        </div>
      </div>
      <div class="hero-kpis">
        <div class="hk">
          <div class="hk-v"><?= (int)($kpiAbs['total_absences']??0) ?></div>
          <div class="hk-l">Absences</div>
          <div class="hk-u"><?= $annee ?></div>
        </div>
        <div class="hk">
          <div class="hk-v"><?= $demandesRestantes ?>/<?= $CONGE_MAX_DEMANDES ?></div>
          <div class="hk-l">Congés dispo</div>
          <div class="hk-u"><?= $annee ?></div>
        </div>
        <div class="hk">
          <div class="hk-v"><?= (int)($kpiForm['total']??0) ?></div>
          <div class="hk-l">Formations</div>
          <div class="hk-u">total</div>
        </div>
        <div class="hk" style="border-radius:0 22px 22px 0">
          <div class="hk-v"><?= number_format((float)($emp['anciennete_ans']??$emp['anciennete_calc']??0),1) ?></div>
          <div class="hk-l">Ancienneté</div>
          <div class="hk-u">années</div>
        </div>
      </div>
    </div>

    <!-- ALERTE CONGÉ -->
    <?php if($nbDemandesAnnee>=$CONGE_MAX_DEMANDES): ?>
    <div class="al al-err"><span class="al-ico">🚫</span><div><strong>Quota atteint —</strong> Vous avez utilisé vos <?= $CONGE_MAX_DEMANDES ?> demandes autorisées pour <?= $annee ?>. Aucune nouvelle demande possible cette année.</div></div>
    <?php elseif($nbDemandesAnnee===$CONGE_MAX_DEMANDES-1): ?>
    <div class="al al-warn"><span class="al-ico">⚠️</span><div><strong>Dernière demande disponible —</strong> Il vous reste <strong>1 seule demande</strong> de congé pour <?= $annee ?>. Maximum <strong><?= $CONGE_MAX_JOURS ?> jours</strong> par demande.</div></div>
    <?php else: ?>
    <div class="al al-ok"><span class="al-ico">✅</span><div><strong><?= $demandesRestantes ?> demande(s) disponible(s)</strong> pour <?= $annee ?> &nbsp;·&nbsp; Limite : <?= $CONGE_MAX_JOURS ?> jours par demande.</div></div>
    <?php endif; ?>

    <!-- SERVICES -->
    <div class="sh"><span class="sl"><span class="sl-bar"></span>Mes services</span></div>
    <div class="mg">
      <a href="index.php?action=declaration_absence" class="mc cr">
        <div class="mc-ico ico-r"><svg fill="none" viewBox="0 0 18 18" stroke-width="1.4"><circle cx="9" cy="7" r="4"/><path d="M2 16C2 12.686 5.134 10 9 10C12.866 10 16 12.686 16 16" stroke-linecap="round"/><path d="M9 13V16M7 15H11" stroke-linecap="round"/></svg></div>
        <div class="mc-t">Déclarer une absence</div>
        <div class="mc-d">Signalez un retard ou une absence.</div>
        <div class="mc-lnk cl-r">Déclarer →</div>
      </a>
      <a href="index.php?action=demande_conge" class="mc cg">
        <div class="mc-ico ico-g"><svg fill="none" viewBox="0 0 18 18" stroke-width="1.4"><rect x="2" y="3" width="14" height="13" rx="2"/><path d="M6 2V4M12 2V4M2 7H16" stroke-linecap="round"/><path d="M6 11L8 13L12 9" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <div class="mc-t">Demande de congé</div>
        <div class="mc-d"><?= $demandesRestantes ?>/<?= $CONGE_MAX_DEMANDES ?> demande(s) restante(s).</div>
        <div class="mc-lnk cl-g">Soumettre →</div>
      </a>
      <a href="index.php?action=planning" class="mc cb">
        <div class="mc-ico ico-b"><svg fill="none" viewBox="0 0 18 18" stroke-width="1.4"><path d="M9 2L15.5 5.5L9 9L2.5 5.5L9 2Z" stroke-linejoin="round"/><path d="M5 8V12C5 12 6.8 14 9 14C11.2 14 13 12 13 12V8" stroke-linecap="round"/></svg></div>
        <div class="mc-t">Mes formations</div>
        <div class="mc-d">Calendrier et parcours.</div>
        <div class="mc-lnk cl-b">Voir →</div>
      </a>
      <a href="index.php?action=mes_documents" class="mc ca">
        <div class="mc-ico ico-a"><svg fill="none" viewBox="0 0 18 18" stroke-width="1.4"><rect x="3" y="2" width="12" height="14" rx="2"/><path d="M6 6H12M6 9H12M6 12H9" stroke-linecap="round"/></svg></div>
        <div class="mc-t">Mes documents</div>
        <div class="mc-d">Bulletins et attestations.</div>
        <div class="mc-lnk cl-a">Télécharger →</div>
      </a>
      <a href="index.php?action=enquetes" class="mc cp">
        <div class="mc-ico ico-p"><svg fill="none" viewBox="0 0 18 18" stroke-width="1.4"><rect x="2" y="2" width="14" height="14" rx="2"/><circle cx="6" cy="7" r="1" fill="#7c3aed"/><circle cx="6" cy="11" r="1" fill="#7c3aed"/><path d="M9 7H15M9 11H13" stroke-linecap="round"/></svg></div>
        <div class="mc-t">Enquêtes</div>
        <div class="mc-d">Sondages internes.</div>
        <div class="mc-lnk cl-p">Accéder →</div>
      </a>
    </div>

    <!-- KPI ABSENCES + CONGÉS côte à côte -->
    <div class="two-col">

      <!-- ABSENCES -->
      <div class="kpi-section">
        <div class="ks-head">
          <div class="ks-title">
            <div class="ks-icon" style="background:#fee2e2;font-size:15px">✕</div>
            Absences <?= $annee ?>
          </div>
          <a href="index.php?action=mes_absences" class="ks-link">Voir tout</a>
        </div>
        <div class="kchips">
          <div class="kchip"><div class="kch-dot dot-v">📋</div><div><div class="kch-v" style="color:#4f46e5"><?= (int)($kpiAbs['total_absences']??0) ?></div><div class="kch-l">Total déclarations</div><div class="kch-s">Année <?= $annee ?></div></div></div>
          <div class="kchip"><div class="kch-dot dot-r">✕</div><div><div class="kch-v" style="color:#e11d48"><?= (int)($kpiAbs['nb_absences']??0) ?></div><div class="kch-l">Absences</div><div class="kch-s">Taux : <?= $tauxAbsenteisme ?>%</div></div></div>
          <div class="kchip"><div class="kch-dot dot-t">📅</div><div><div class="kch-v" style="font-size:14px;color:#0d9488;margin-top:2px"><?= fdate($kpiAbs['derniere']??null) ?></div><div class="kch-l">Dernière absence</div><div class="kch-s"><?= fdate($kpiAbs['premiere']??null) ?></div></div></div>
        </div>
        <div class="tw">
          <div class="tw-hd"><span class="tw-label">Historique</span><span class="tw-chip">8 dernières</span></div>
          <table>
            <thead><tr><th>Date</th><th>Type</th><th>Séance</th><th>Motif</th></tr></thead>
            <tbody>
              <?php if(empty($listeAbsences)): ?>
                <tr class="ec"><td colspan="4">Aucune absence en <?= $annee ?></td></tr>
              <?php else: foreach($listeAbsences as $a): ?>
              <tr>
                <td style="font-family:'DM Mono',monospace;font-size:11px"><?= fdate($a['date_abs']) ?></td>
                <td><?= typeBadge($a['type_declaration']??'absence') ?></td>
                <td class="tm"><?= htmlspecialchars($a['heure_seance']??'—') ?></td>
                <td class="tm" style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($a['motif']??'—') ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CONGÉS -->
      <div class="kpi-section">
        <div class="ks-head">
          <div class="ks-title">
            <div class="ks-icon" style="background:#d1fae5;font-size:15px">✈</div>
            Congés <?= $annee ?>
          </div>
          <a href="index.php?action=mes_conges" class="ks-link">Voir tout</a>
        </div>
        <div class="kchips">
          <div class="kchip"><div class="kch-dot dot-g">✓</div><div><div class="kch-v" style="color:#16a34a"><?= (int)($kpiConge['jours_pris']??0) ?></div><div class="kch-l">Jours pris</div><div class="kch-s">Sur <?= $droitAnnuel ?> j / an</div></div></div>
          <div class="kchip"><div class="kch-dot dot-a">⏳</div><div><div class="kch-v" style="color:#d97706"><?= (int)($kpiConge['en_attente']??0) ?></div><div class="kch-l">En attente</div><div class="kch-s">À traiter</div></div></div>
          <div class="kchip"><div class="kch-dot dot-r">✕</div><div><div class="kch-v" style="color:#e11d48"><?= (int)($kpiConge['refusees']??0) ?></div><div class="kch-l">Refusées</div><div class="kch-s">Cette année</div></div></div>
          <div class="kchip"><div class="kch-dot dot-fu">📝</div><div><div class="kch-v" style="color:#a21caf"><?= $demandesRestantes ?>/<?= $CONGE_MAX_DEMANDES ?></div><div class="kch-l">Demandes restantes</div><div class="kch-s"><?= $CONGE_MAX_JOURS ?> j max / demande</div></div></div>
        </div>
        <div class="tw">
          <div class="tw-hd"><span class="tw-label">Historique</span><span class="tw-chip">8 dernières</span></div>
          <table>
            <thead><tr><th>Du</th><th>Au</th><th>Jours</th><th>Statut</th></tr></thead>
            <tbody>
              <?php if(empty($listeConges)): ?>
                <tr class="ec"><td colspan="4">Aucune demande cette année</td></tr>
              <?php else: foreach($listeConges as $c): ?>
              <?php $nbJ=(int)$c['nb_jours'];$over=$nbJ>$CONGE_MAX_JOURS; ?>
              <tr <?= $over?'style="background:#fff5f5"':'' ?>>
                <td style="font-family:'DM Mono',monospace;font-size:11px"><?= fdate($c['date_debut']) ?></td>
                <td style="font-family:'DM Mono',monospace;font-size:11px"><?= fdate($c['date_fin']) ?></td>
                <td><?= $nbJ ?> j<?= $over?' <span style="color:var(--ro);font-size:9px;font-weight:700"> ⚠</span>':'' ?></td>
                <td><?= $c['statut']?statutBadge($c['statut']):statutBadge('en attente') ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

   
</div>
</body>
</html>
