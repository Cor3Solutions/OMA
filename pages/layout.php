<?php
function thfp_head($title='Tribal Hunters Fight Promotion'){?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title);?> | THFP</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800;900&family=Barlow:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
    --red:#CC0000;--red-d:#A80000;--ember:#FF8C00;--gold:#D4AF37;
    --red-bg:rgba(204,0,0,.06);--red-border:rgba(204,0,0,.18);
    --win:#1A7A3C;--win-l:#27AE60;--win-bg:rgba(26,122,60,.08);
    --loss:#CC0000;--draw:#B8800A;--draw-bg:rgba(184,128,10,.08);--blue:#1B5EA8;
    --bg:#EDEDED;--white:#FFFFFF;--gray1:#F5F5F5;--gray2:#EBEBEB;
    --border:#CCCCCC;--border2:#BBBBBB;
    --hero-bg:#0C0B0D;--hero-border:rgba(204,0,0,.22);
    --hero-ink:#F0EEF4;--hero-sub:rgba(240,238,244,.6);
    --ink:#1A1A1A;--sub:#555;--dim:#888;
    --font-c:'Barlow Condensed',sans-serif;--font-r:'Barlow',sans-serif;--font-m:'Roboto Mono',monospace;
    --nav-h:54px;--max:1280px;--ease:.15s ease;
}
html{scroll-behavior:smooth;overflow-x:hidden;}
body{background:var(--bg);color:var(--ink);font-family:var(--font-r);font-size:15px;line-height:1.55;-webkit-font-smoothing:antialiased;overflow-x:hidden;}
::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:#e0e0e0;}::-webkit-scrollbar-thumb{background:var(--border2);}
a{color:var(--ink);text-decoration:none;}a:hover{color:var(--red);}img{display:block;max-width:100%;}
button{font-family:var(--font-r);cursor:pointer;}
.util-bar{background:#111;height:26px;display:flex;align-items:center;}
.util-inner{max-width:var(--max);margin:0 auto;width:100%;padding:0 12px;display:flex;align-items:center;justify-content:flex-end;gap:10px;}
.util-a{font-family:var(--font-c);font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#777;transition:color var(--ease);}
.util-a:hover{color:#fff;}
.util-sep{color:#333;}
.mainnav{background:var(--white);border-bottom:3px solid var(--red);position:sticky;top:0;z-index:300;box-shadow:0 2px 8px rgba(0,0,0,.1);}
.mainnav-inner{max-width:var(--max);margin:0 auto;height:var(--nav-h);display:flex;align-items:center;padding:0 12px;overflow:hidden;}
.nav-brand{display:flex;align-items:center;margin-right:auto;flex-shrink:0;text-decoration:none;}
.nav-logo{height:clamp(26px,5vw,34px);width:auto;display:block;object-fit:contain;max-width:160px;}
.nav-links{display:flex;height:var(--nav-h);align-items:stretch;}
.nav-a{font-family:var(--font-c);font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--sub);padding:0 16px;display:flex;align-items:center;border-bottom:3px solid transparent;margin-bottom:-3px;transition:all var(--ease);white-space:nowrap;}
.nav-a:hover{color:var(--ink);background:var(--gray1);}
.nav-a.on{color:var(--red);border-bottom-color:var(--red);}
.hb{display:none;flex-direction:column;gap:4px;background:none;border:none;padding:8px;flex-shrink:0;}
.hb span{display:block;width:20px;height:2px;background:var(--ink);border-radius:1px;transition:all .25s;}
.hb.open span:nth-child(1){transform:rotate(45deg) translate(4px,4px);}
.hb.open span:nth-child(2){opacity:0;}
.hb.open span:nth-child(3){transform:rotate(-45deg) translate(4px,-4px);}
.mob-menu{display:none;position:fixed;top:var(--nav-h);left:0;right:0;z-index:290;background:var(--white);border-bottom:1px solid var(--border);box-shadow:0 4px 12px rgba(0,0,0,.12);overflow-x:hidden;}
.mob-menu.open{display:block;}
.mob-menu .nav-a{height:52px;padding:0 20px;border-bottom:1px solid var(--gray2);margin-bottom:0;display:flex;border-right:none;font-size:16px;}
/* HERO */
.hero{position:relative;overflow:hidden;padding:2.5rem 1rem 2rem;}
.hero-video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;pointer-events:none;}
.hero-overlay{position:absolute;inset:0;z-index:1;background:rgba(0,0,0,.78);}
.hero-overlay::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 50% 110%,rgba(204,0,0,.28),transparent);}
.hero-grid{position:absolute;inset:0;z-index:2;background-image:linear-gradient(rgba(204,0,0,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(204,0,0,.04) 1px,transparent 1px);background-size:50px 50px;mask-image:radial-gradient(ellipse 90% 80% at 50% 100%,black,transparent);}
.hero-inner{max-width:var(--max);margin:0 auto;position:relative;z-index:3;display:flex;flex-direction:column;align-items:center;text-align:center;}
.hero-eyebrow{font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--red);margin-bottom:.8rem;display:inline-flex;align-items:center;gap:.5rem;border:1px solid var(--hero-border);padding:4px 16px;background:rgba(204,0,0,.06);}
.hero-eyebrow::before,.hero-eyebrow::after{content:'◆';font-size:.5rem;color:var(--ember);opacity:.7;}
.hero-logo-wrap{margin-bottom:.9rem;}
.hero-logo{height:60px;width:auto;object-fit:contain;filter:drop-shadow(0 0 18px rgba(204,0,0,.4)) brightness(1.1);}
.hero-title{font-family:var(--font-c);font-size:clamp(1.8rem,7vw,5rem);font-weight:900;text-transform:uppercase;letter-spacing:1px;line-height:.92;color:var(--hero-ink);margin-bottom:.35rem;}
.hero-title .fire{background:linear-gradient(135deg,var(--red) 0%,var(--ember) 60%,var(--gold) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;filter:drop-shadow(0 0 20px rgba(204,0,0,.4));}
.hero-sub{font-family:var(--font-c);font-size:clamp(.8rem,2vw,1rem);color:var(--hero-sub);letter-spacing:2.5px;text-transform:uppercase;margin-bottom:1.25rem;}
.flame-rule{display:flex;align-items:center;gap:10px;margin:0 auto .8rem;max-width:200px;}
.flame-line{flex:1;height:1px;background:linear-gradient(to right,transparent,var(--red));}
.flame-line.rev{background:linear-gradient(to left,transparent,var(--red));}
.hero-stats{display:inline-flex;border:1px solid var(--hero-border);background:rgba(204,0,0,.04);}
.hero-stat{padding:.85rem 1.5rem;border-right:1px solid var(--hero-border);text-align:center;position:relative;}
.hero-stat:last-child{border-right:none;}
.hero-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--red),var(--ember));}
.hs-v{font-family:var(--font-c);font-size:2rem;font-weight:900;color:var(--red);line-height:1;display:block;filter:drop-shadow(0 0 6px rgba(204,0,0,.35));}
.hs-l{font-family:var(--font-c);font-size:.58rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--hero-sub);display:block;margin-top:2px;}
/* FILTER BAR */
.filter-bar{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:var(--nav-h);z-index:100;}
.filter-bar-inner{max-width:var(--max);margin:0 auto;padding:5px 16px;display:flex;align-items:center;gap:4px;}
.ftab{font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:5px 14px;border-radius:2px;border:none;cursor:pointer;background:transparent;color:var(--sub);transition:all var(--ease);}
.ftab.on{background:var(--red);color:#fff;}
.ftab:hover:not(.on){background:var(--gray2);color:var(--ink);}
.filter-bar-count{margin-left:auto;font-size:11px;color:var(--dim);}
/* LAYOUT */
.wrap{max-width:var(--max);margin:0 auto;padding:12px 16px;}
.cols{display:grid;grid-template-columns:1fr 230px;gap:14px;align-items:start;}
@media(max-width:960px){.cols{grid-template-columns:1fr;}}
/* SECTION HEADS */
.sec-hd{background:#1A1A1A;color:#fff;padding:7px 10px;font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;}
/* BOXES */
.box{background:var(--white);border:1px solid var(--border);margin-bottom:10px;}
/* PILLS / BADGES */
.pill{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;padding:2px 8px;border-radius:2px;display:inline-block;}
.p-done{background:var(--win-bg);color:var(--win);border:1px solid rgba(26,122,60,.2);}
.p-soon{background:rgba(27,94,168,.08);color:var(--blue);border:1px solid rgba(27,94,168,.2);}
.p-live{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border);}
.p-gone{background:var(--gray2);color:var(--dim);border:1px solid var(--border);}
.rb{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;padding:2px 8px;border-radius:2px;display:inline-block;}
.rb-w{background:var(--win-bg);color:var(--win);border:1px solid rgba(26,122,60,.2);}
.rb-l{background:var(--red-bg);color:var(--red);border:1px solid rgba(204,0,0,.2);}
.rb-d{background:var(--draw-bg);color:var(--draw);border:1px solid rgba(184,128,10,.2);}
.rb-nc{background:var(--gray2);color:var(--sub);border:1px solid var(--border);}
.mth{font-family:var(--font-c);font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.5px;}
.disc{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;padding:1px 6px;border-radius:2px;}
.disc-mt{background:rgba(204,0,0,.08);color:var(--red);border:1px solid rgba(204,0,0,.18);}
.disc-kb{background:rgba(184,128,10,.08);color:var(--draw);border:1px solid rgba(184,128,10,.18);}
.disc-mma{background:rgba(99,60,180,.08);color:#6B4BC8;border:1px solid rgba(99,60,180,.18);}
.tag{font-family:var(--font-c);font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;padding:1px 6px;border-radius:2px;border:1px solid var(--border);background:var(--gray1);color:var(--sub);}
.tag-m{border-color:rgba(27,94,168,.25);background:rgba(27,94,168,.05);color:var(--blue);}
.tag-f{border-color:rgba(180,30,100,.25);background:rgba(180,30,100,.05);color:#B41E64;}
.tag-jr{border-color:rgba(184,128,10,.25);background:rgba(184,128,10,.05);color:var(--draw);}
.tag-sr{border-color:rgba(26,122,60,.25);background:rgba(26,122,60,.05);color:var(--win);}
.rec{font-family:var(--font-m);font-size:11px;white-space:nowrap;}
.rec .w{color:var(--win);font-weight:700;}.rec .s{color:var(--dim);}.rec .l{color:var(--red);font-weight:700;}.rec .d{color:var(--draw);}
/* FIGHTER MODAL */
.fmo{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.7);align-items:flex-start;justify-content:center;padding:20px 16px;overflow-y:auto;}
.fmo.open{display:flex;}
.fmb{background:var(--white);border:1px solid var(--border);width:100%;max-width:700px;box-shadow:0 12px 40px rgba(0,0,0,.3);margin:auto;animation:fms .2s ease;}
@keyframes fms{from{transform:translateY(-10px);opacity:0}to{transform:none;opacity:1}}
.fm-top{background:#111;display:flex;align-items:stretch;min-height:140px;}
.fm-photo{width:130px;flex-shrink:0;background:#1a1a1a;display:grid;place-items:center;overflow:hidden;}
.fm-photo img{width:100%;height:100%;object-fit:cover;}
.fm-photo-init{font-family:var(--font-c);font-size:2.8rem;font-weight:900;color:#333;}
.fm-hinfo{flex:1;padding:14px 16px;display:flex;flex-direction:column;justify-content:flex-end;}
.fm-hnat{font-family:var(--font-c);font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#666;margin-bottom:3px;}
.fm-hname{font-family:var(--font-c);font-size:26px;font-weight:900;text-transform:uppercase;color:#fff;line-height:1;margin-bottom:3px;}
.fm-hnick{font-size:12px;color:#888;font-style:italic;margin-bottom:6px;}
.fm-hgym{font-family:var(--font-c);font-size:11px;font-weight:700;text-transform:uppercase;color:var(--red);letter-spacing:.5px;}
.fm-htags{display:flex;flex-wrap:wrap;gap:4px;margin-top:8px;}
.fm-recbar{background:var(--red);padding:8px 14px;display:flex;align-items:center;gap:18px;}
.fm-ri{text-align:center;}
.fm-rv{font-family:var(--font-c);font-size:22px;font-weight:900;color:#fff;line-height:1;display:block;}
.fm-rl{font-family:var(--font-c);font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.55);display:block;margin-top:1px;}
.fm-rsep{width:1px;background:rgba(255,255,255,.2);align-self:stretch;}
.fm-wc-right{margin-left:auto;text-align:right;}
.fm-wc-lbl{font-family:var(--font-c);font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.5);}
.fm-wc-val{font-family:var(--font-c);font-size:18px;font-weight:900;color:#fff;}
.fm-dstrip{display:grid;grid-template-columns:1fr 1fr 1fr;border-bottom:1px solid var(--border);}
.fm-dc{padding:8px 10px;border-right:1px solid var(--border);text-align:center;position:relative;}
.fm-dc:last-child{border-right:none;}
.fm-dc::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.fm-dc.mt::before{background:var(--red);}.fm-dc.kb::before{background:var(--draw);}.fm-dc.mm::before{background:#6B4BC8;}
.fm-dn{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);margin-bottom:4px;display:block;}
.fm-dr{font-family:var(--font-m);font-size:16px;font-weight:700;}
.fm-dr .w{color:var(--win);}.fm-dr .s{color:var(--dim);}.fm-dr .l{color:var(--red);}.fm-dr .d{color:var(--draw);}
.fm-shd{background:var(--gray2);padding:7px 14px;font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--sub);border-bottom:1px solid var(--border);border-top:1px solid var(--border);}
.fm-sg{display:grid;grid-template-columns:1fr 1fr;}
.fm-sc{padding:8px 12px;border-right:1px solid var(--gray2);border-bottom:1px solid var(--gray2);}
.fm-sc:nth-child(even){border-right:none;}
.fm-sc.full{grid-column:1/-1;border-right:none;}
.fm-sl{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);margin-bottom:3px;}
.fm-sv{font-size:15px;font-weight:500;color:var(--ink);line-height:1.3;}
.fm-sv.empty{color:var(--dim);font-style:italic;font-size:13px;}
.fm-hr{display:grid;grid-template-columns:50px 1fr auto;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--gray2);transition:background var(--ease);}
.fm-hr:last-child{border-bottom:none;}
.fm-hr:hover{background:var(--gray1);}
.fm-hopp-n{font-size:15px;font-weight:600;}
.fm-hopp-g{font-size:12px;color:var(--dim);margin-top:2px;}
.fm-hdisc{margin-top:2px;}
.fm-hres{text-align:right;}
.fm-hmet{font-family:var(--font-c);font-size:13px;font-weight:600;color:var(--sub);text-transform:uppercase;}
.fm-hev{font-size:12px;color:var(--dim);margin-top:3px;}
.fm-cbar{background:#1A1A1A;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10;}
.fm-cbtn{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#aaa;background:none;border:1px solid #444;padding:8px 16px;cursor:pointer;transition:all var(--ease);border-radius:2px;min-height:38px;}
.fm-cbtn:hover{background:var(--red);border-color:var(--red);color:#fff;}
/* FOOTER */
footer{background:#111;color:#666;padding:20px 16px;margin-top:20px;}
.foot-inner{max-width:var(--max);margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.foot-logo{height:26px;width:auto;object-fit:contain;opacity:.55;filter:brightness(0) invert(1);}
footer p{font-size:11px;}
/* ── RESPONSIVE ─────────────────────────────────────────────────────── */
/* Tablet */
@media(max-width:960px){
    .nav-links{display:none;}.hb{display:flex;}
    .cols{grid-template-columns:1fr;}
}
/* Mobile */
@media(max-width:700px){
    .util-bar{display:none;}
    .wrap{padding:8px;}
    /* Hero */
    .hero{padding:2rem .75rem 1.75rem;}
    .hero-logo{height:44px;}
    .hero-eyebrow{font-size:10px;letter-spacing:2px;padding:4px 10px;}
    .hero-title{font-size:clamp(2rem,10vw,3.5rem);}
    .hero-sub{font-size:.7rem;letter-spacing:1.5px;}
    .hero-stats{width:100%;flex-wrap:wrap;}
    .hero-stat{flex:1;min-width:80px;padding:.7rem .5rem;}
    .hs-v{font-size:1.55rem;}
    .hs-l{font-size:.5rem;letter-spacing:1.5px;}
    /* Filter bar */
    .filter-bar-inner{flex-wrap:wrap;gap:4px;padding:6px 8px;}
    .ftab{padding:6px 12px;font-size:12px;}
    .filter-bar-count{width:100%;text-align:center;order:99;margin-left:0;}
    /* Modal — full responsive */
    .fmo{padding:0;}
    .fmb{max-height:100vh;overflow-y:auto;border-radius:0;margin:0;border:none;
         display:flex;flex-direction:column;}
    .fm-top{flex-direction:row;min-height:130px;}
    .fm-photo{width:110px;height:auto;min-height:130px;}
    .fm-hname{font-size:18px;}
    .fm-hgym{font-size:11px;}
    .fm-recbar{padding:8px 12px;gap:12px;flex-wrap:nowrap;}
    .fm-rv{font-size:20px;}
    .fm-wc-right{display:none;} /* hide weight class on small to save space */
    .fm-dstrip{grid-template-columns:1fr 1fr 1fr;}
    .fm-dr{font-size:13px;}
    .fm-dn{font-size:9px;}
    .fm-sg{grid-template-columns:1fr 1fr;}
    .fm-sc{padding:8px 10px;}
    .fm-sc:nth-child(odd){border-right:1px solid var(--gray2);}
    .fm-sc:nth-child(even){border-right:none;}
    .fm-sc.full{grid-column:1/-1;border-right:none;}
    .fm-sl{font-size:9px;}
    .fm-sv{font-size:14px;}
    .fm-hr{grid-template-columns:40px 1fr auto;gap:6px;padding:8px 10px;}
    .fm-hopp-n{font-size:14px;}
    .fm-hmet{font-size:12px;}
    .fm-cbar{padding:10px 12px;}
    .fm-cbtn{padding:8px 14px;font-size:12px;}
}
/* Very small */
@media(max-width:380px){
    .hero-stat{min-width:70px;}
    .hs-v{font-size:1.35rem;}
    .fm-top{flex-direction:column;}
    .fm-photo{width:100%;height:110px;min-height:0;}
    .fm-sg{grid-template-columns:1fr;}
    .fm-sc{border-right:none!important;}
}
</style>
<?php }

function thfp_nav($cur=''){
    $nav=['thfp'=>['Events','thfp.php'],'fighters'=>['Fighters','fighters.php'],'rankings'=>['Rankings','rankings.php'],'analytics'=>['Analytics','analytics.php'],'aboutthfp'=>['About','aboutthfp.php']];
?>
<div class="util-bar"><div class="util-inner"><a href=" " class="util-a"> </a><span class="util-sep"></span><a href="" class="util-a"></a></div></div>
<nav class="mainnav"><div class="mainnav-inner">
    <a href="thfp.php" class="nav-brand"><img src="../assets/images/thfp.png" alt="THFP" class="nav-logo"></a>
    <div class="nav-links"><?php foreach($nav as $k=>[$l,$u]):?><a href="<?php echo $u;?>" class="nav-a <?php echo $cur===$k?'on':'';?>"><?php echo $l;?></a><?php endforeach;?></div>
    <button class="hb" id="hb"><span></span><span></span><span></span></button>
</div></nav>
<div class="mob-menu" id="mobMenu"><?php foreach($nav as $k=>[$l,$u]):?><a href="<?php echo $u;?>" class="nav-a <?php echo $cur===$k?'on':'';?>"><?php echo $l;?></a><?php endforeach;?></div>
<?php }

function thfp_hero($te,$tb,$tf,$fr){?>
<section class="hero">
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
        <source src="combat.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-grid"></div>
    <div class="hero-inner">
        <div class="hero-eyebrow">Quezon City, Philippines &middot; Est. 2026</div>
        <div class="hero-logo-wrap"><img src="../assets/images/thfp.png" alt="Tribal Hunters Fight Promotion" class="hero-logo"></div>
        <div class="hero-title"><span class="fire">Tribal Hunters</span></div>
        <div class="hero-sub">Fight Promotion &mdash; Where Warriors Are Forged</div>
        <div class="flame-rule"><div class="flame-line"></div><span style="color:var(--ember);font-size:.9rem;">🔥</span><div class="flame-line rev"></div></div>
        <div class="hero-stats">
            <div class="hero-stat"><span class="hs-v"><?php echo $te;?></span><span class="hs-l">Events</span></div>
            <div class="hero-stat"><span class="hs-v"><?php echo $tb;?></span><span class="hs-l">Bouts</span></div>
            <div class="hero-stat"><span class="hs-v"><?php echo $tf;?></span><span class="hs-l">Fighters</span></div>
            <div class="hero-stat"><span class="hs-v"><?php echo $fr;?>%</span><span class="hs-l">Finish Rate</span></div>
        </div>
    </div>
</section>
<?php }

function thfp_fighter_modal(){?>
<div class="fmo" id="fmo" onclick="if(event.target===this)cf()">
<div class="fmb">
    <div class="fm-cbar"><span style="font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#aaa;" id="fmPromo">THFP Fighter</span><button class="fm-cbtn" onclick="cf()">&#10005; Close</button></div>
    <div class="fm-top">
        <div class="fm-photo" id="fmPhoto"><div class="fm-photo-init" id="fmInit">?</div></div>
        <div class="fm-hinfo">
            <div class="fm-hnat" id="fmNat">Philippines</div>
            <div class="fm-hname" id="fmName">—</div>
            <div class="fm-hnick" id="fmNick" style="display:none"></div>
            <div class="fm-hgym" id="fmGym">—</div>
            <div class="fm-htags" id="fmTags"></div>
        </div>
    </div>
    <div class="fm-recbar">
        <div class="fm-ri"><span class="fm-rv" id="fmW">0</span><span class="fm-rl">Wins</span></div>
        <div class="fm-rsep"></div>
        <div class="fm-ri"><span class="fm-rv" id="fmL" style="color:#ffcccc;">0</span><span class="fm-rl">Losses</span></div>
        <div class="fm-rsep"></div>
        <div class="fm-ri"><span class="fm-rv" id="fmD">0</span><span class="fm-rl">Draws</span></div>
        <div class="fm-wc-right"><div class="fm-wc-lbl">Weight Class</div><div class="fm-wc-val" id="fmWC">—</div></div>
    </div>
    <div class="fm-dstrip">
        <div class="fm-dc mt"><span class="fm-dn">Muay Thai</span><div class="fm-dr"><span class="w" id="fmMTW">0</span><span class="s">-</span><span class="l" id="fmMTL">0</span><span class="s">-</span><span class="d" id="fmMTD">0</span></div></div>
        <div class="fm-dc kb"><span class="fm-dn">Kickboxing</span><div class="fm-dr"><span class="w" id="fmKBW">0</span><span class="s">-</span><span class="l" id="fmKBL">0</span><span class="s">-</span><span class="d" id="fmKBD">0</span></div></div>
        <div class="fm-dc mm"><span class="fm-dn">MMA</span><div class="fm-dr"><span class="w" id="fmMMAW">0</span><span class="s">-</span><span class="l" id="fmMMAL">0</span><span class="s">-</span><span class="d" id="fmMMAD">0</span></div></div>
    </div>
    <div class="fm-body">
        <div class="fm-shd">Fighter Details</div>
        <div class="fm-sg">
            <div class="fm-sc"><div class="fm-sl">Division</div><div class="fm-sv" id="fmDiv">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Date of Birth</div><div class="fm-sv" id="fmDOB">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Age</div><div class="fm-sv" id="fmAge">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Nationality</div><div class="fm-sv" id="fmNat2">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Height</div><div class="fm-sv" id="fmH">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Reach</div><div class="fm-sv" id="fmR">—</div></div>
            <div class="fm-sc full"><div class="fm-sl">Hometown</div><div class="fm-sv" id="fmTown">—</div></div>
        </div>
        <div class="fm-shd">Fight History</div>
        <div id="fmHist"></div>
    </div>
</div>
</div>
<?php }

function thfp_fighter_js($fmap,$bh){
    echo '<script>';
    echo 'var FM={},BH='.json_encode($bh,JSON_HEX_TAG).';';
    echo 'var FD='.json_encode(array_values($fmap),JSON_HEX_TAG).';';
    echo 'FD.forEach(function(f){FM[f.id]=f;});';
    ?>
function ini(n){var p=(n||'').trim().split(/\s+/).filter(Boolean);return((p[0]||'')[0]+(p.length>1?(p[p.length-1]||'')[0]:'')).toUpperCase()||'?';}
function cap(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function sv(id,v){var e=document.getElementById(id);if(!e)return;e.textContent=v||'—';e.className='fm-sv'+(v?'':' empty');}
function ml(m){return{KO:'KO',TKO:'TKO',RSC:'RSC',decision:'Decision',no_contest:'No Contest',DRAW:'Draw'}[m]||(m?m.toUpperCase():'—');}
function calcRec(id){var rec={muay_thai:{w:0,l:0,d:0},kickboxing:{w:0,l:0,d:0},mma:{w:0,l:0,d:0}};(BH[id]||[]).forEach(function(b){var disc=b.discipline||'muay_thai';if(!rec[disc])rec[disc]={w:0,l:0,d:0};var myN=(id==b.red_id)?b.red_name:b.blue_name;var won=b.winner_name&&b.winner_name===myN;var lost=b.winner_name&&b.winner_name!==myN;if(won)rec[disc].w++;else if(b.result_method==='DRAW')rec[disc].d++;else if(b.result_method!=='no_contest'&&lost)rec[disc].l++;});return rec;}
function of(id){
    var f=FM[id];if(!f)return;
    var ph=document.getElementById('fmPhoto'),init=document.getElementById('fmInit');
    if(f.photo_path){ph.innerHTML='';var img=new Image();img.src=f.photo_path;img.style.cssText='width:100%;height:100%;object-fit:cover;display:block;';img.onerror=function(){ph.innerHTML='';init.textContent=ini(f.name);ph.appendChild(init);};ph.appendChild(img);}
    else{ph.innerHTML='';init.textContent=ini(f.name);ph.appendChild(init);}
    document.getElementById('fmName').textContent=f.name;document.getElementById('fmPromo').textContent=(f.gym||'THFP')+' Fighter';
    var nk=document.getElementById('fmNick');nk.style.display=f.nickname?'':'none';if(f.nickname)nk.textContent='"'+f.nickname+'"';
    document.getElementById('fmGym').textContent=f.gym||'—';document.getElementById('fmNat').textContent=f.nationality||'Philippines';
    var tags=document.getElementById('fmTags');tags.innerHTML='';
    [[f.weight_class?f.weight_class+' kg':null,'tag'],[f.gender?cap(f.gender):null,'tag tag-'+(f.gender==='female'?'f':'m')],[f.age_category?cap(f.age_category):null,'tag tag-'+(f.age_category&&f.age_category.toLowerCase()==='senior'?'sr':'jr')]].forEach(function(t){if(t[0]){var s=document.createElement('span');s.className=t[1];s.textContent=t[0];tags.appendChild(s);}});
    var rec=calcRec(id);
    var tw=rec.muay_thai.w+rec.kickboxing.w+rec.mma.w,tl=rec.muay_thai.l+rec.kickboxing.l+rec.mma.l,td=rec.muay_thai.d+rec.kickboxing.d+rec.mma.d;
    document.getElementById('fmW').textContent=tw;document.getElementById('fmL').textContent=tl;document.getElementById('fmD').textContent=td;
    document.getElementById('fmWC').textContent=f.weight_class?f.weight_class+' kg':'—';
    ['MT','KB','MMA'].forEach(function(d,i){var disc=['muay_thai','kickboxing','mma'][i];document.getElementById('fm'+d+'W').textContent=rec[disc].w;document.getElementById('fm'+d+'L').textContent=rec[disc].l;document.getElementById('fm'+d+'D').textContent=rec[disc].d;});
    sv('fmDiv',[cap(f.age_category),cap(f.gender)].filter(Boolean).join(' / ')||null);sv('fmH',f.height||null);sv('fmR',f.reach||null);sv('fmNat2',f.nationality||null);sv('fmTown',f.hometown||null);
    var dob=document.getElementById('fmDOB'),age=document.getElementById('fmAge');
    if(f.date_of_birth){var d=new Date(f.date_of_birth);dob.textContent=d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});dob.className='fm-sv';age.textContent=Math.floor((new Date()-d)/31557600000)+' yrs';age.className='fm-sv';}
    else{dob.textContent='—';dob.className='fm-sv empty';age.textContent='—';age.className='fm-sv empty';}
    var hist=document.getElementById('fmHist');hist.innerHTML='';
    var bouts=BH[id]||[];
    if(!bouts.length){hist.innerHTML='<div style="padding:12px;color:var(--dim);font-size:11px;font-style:italic;">No recorded bouts.</div>';}
    else{bouts.forEach(function(b){var myN=(id==b.red_id)?b.red_name:b.blue_name;var won=b.winner_name&&b.winner_name===myN;var lost=b.winner_name&&!won&&b.result_method!=='no_contest';var opp=id==b.red_id?b.blue_name:b.red_name;var og=id==b.red_id?b.blue_gym:b.red_gym;var dl={'muay_thai':'Muay Thai','kickboxing':'Kickboxing','mma':'MMA'}[b.discipline||'muay_thai']||'Muay Thai';var dsc=b.discipline==='kickboxing'?'kb':b.discipline==='mma'?'mma':'mt';var bc=won?'rb-w':lost?'rb-l':'rb-nc';var row=document.createElement('div');row.className='fm-hr';row.innerHTML='<div style="text-align:center;"><span class="rb '+bc+'">'+(won?'Win':lost?'Loss':'NC')+'</span></div><div><div class="fm-hopp-n">vs. '+(opp||'TBA')+'</div>'+(og?'<div class="fm-hopp-g">'+og+'</div>':'')+'<div class="fm-hdisc"><span class="disc disc-'+dsc+'">'+dl+'</span></div></div><div class="fm-hres"><div class="fm-hmet">'+ml(b.result_method)+(b.result_round?' · R'+b.result_round:'')+'</div><div class="fm-hev">C'+b.event_number+' · '+cap(b.bout_type)+'</div></div>';hist.appendChild(row);});}
    document.getElementById('fmo').classList.add('open');document.body.style.overflow='hidden';
}
function cf(){document.getElementById('fmo').classList.remove('open');document.body.style.overflow='';}
<?php
    echo '</script>';
}

function thfp_foot(){?>
<footer>
    <div class="foot-inner">
        <img src="../assets/images/thfp.png" alt="THFP" class="foot-logo">
        <p>Oriental Muayboran Academy &middot; Quezon City, Philippines &middot; &copy; <?php echo date('Y');?></p>
    </div>
</footer>
<script>
var hb=document.getElementById('hb'),mm=document.getElementById('mobMenu');
if(hb)hb.addEventListener('click',function(){hb.classList.toggle('open');mm.classList.toggle('open');});
document.addEventListener('click',function(e){if(hb&&mm&&!hb.contains(e.target)&&!mm.contains(e.target)){hb.classList.remove('open');mm.classList.remove('open');}});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){hb&&hb.classList.remove('open');mm&&mm.classList.remove('open');var m=document.getElementById('fmo');if(m){m.classList.remove('open');document.body.style.overflow='';}}});
</script>
<?php }
?>