<script>
// ── Dynamic Gochar panel ────────────────────────────────────────────────
let _goMode = 'date';
let _goInit = false;
let _goLoading = false;

function _goPad(n){ return String(n).padStart(2,'0'); }
function _goTodayISO(){ const d=new Date(); return d.getFullYear()+'-'+_goPad(d.getMonth()+1)+'-'+_goPad(d.getDate()); }

function initGocharPanel(){
  if (_masaLat === null) return;            // need a calculated chart
  if (_goInit) return;
  _goInit = true;
  const today = new Date();
  const di = document.getElementById('goDate');
  const mi = document.getElementById('goMonth');
  const yi = document.getElementById('goYear');
  if (di && !di.value) di.value = _goTodayISO();
  if (mi && !mi.value) mi.value = today.getFullYear()+'-'+_goPad(today.getMonth()+1);
  if (yi && !yi.value) yi.value = today.getFullYear();
  gocharFetch();
}

function setGoMode(m){
  _goMode = m;
  ['date','month','year'].forEach(x=>{
    const b = document.getElementById('goMode_'+x);
    if (b){ b.style.background = x===m?'var(--sky)':'transparent'; b.style.color = x===m?'#fff':'var(--text-mid)'; }
  });
  document.getElementById('goDate').style.display  = m==='date'  ? '' : 'none';
  document.getElementById('goMonth').style.display = m==='month' ? '' : 'none';
  document.getElementById('goYear').style.display  = m==='year'  ? '' : 'none';
  gocharFetch();
}

function goShift(dir){
  if (_goMode === 'date'){
    const el=document.getElementById('goDate');
    const d=new Date((el.value||_goTodayISO())+'T00:00:00');
    d.setDate(d.getDate()+dir);
    el.value=d.getFullYear()+'-'+_goPad(d.getMonth()+1)+'-'+_goPad(d.getDate());
  } else if (_goMode === 'month'){
    const el=document.getElementById('goMonth');
    let [y,m]=(el.value||'').split('-').map(Number);
    if(!y){const t=new Date();y=t.getFullYear();m=t.getMonth()+1;}
    m+=dir; if(m<1){m=12;y--;} if(m>12){m=1;y++;}
    el.value=y+'-'+_goPad(m);
  } else {
    const el=document.getElementById('goYear');
    el.value=(parseInt(el.value||new Date().getFullYear())+dir);
  }
  gocharFetch();
}

async function gocharFetch(){
  if (_masaLat === null) return;
  if (_goLoading) return;
  _goLoading = true;

  // Build natal payload from the form
  const date = document.getElementById('dateInput').value;
  const time = document.getElementById('timeInput').value;
  const off  = parseFloat(document.getElementById('utcOffset').value);
  const lat  = parseFloat(document.getElementById('lat').value);
  const lon  = parseFloat(document.getElementById('lon').value);

  // Resolve target date for the chosen mode
  let target;
  if (_goMode === 'date'){
    target = document.getElementById('goDate').value || _goTodayISO();
  } else if (_goMode === 'month'){
    const mv = document.getElementById('goMonth').value || (new Date().getFullYear()+'-'+_goPad(new Date().getMonth()+1));
    target = mv + '-01';
  } else {
    const yv = parseInt(document.getElementById('goYear').value || new Date().getFullYear());
    target = yv + '-01-01';
  }

  const content = document.getElementById('gocharContent');
  content.innerHTML = '<div style="text-align:center;padding:48px;color:var(--text-lt)"><span style="font-size:1.8rem">🪐</span><br>Calculating transits…</div>';

  try {
    const res = await fetch('{{ route("astro.gochar") }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({date,time,utcOffset:off,lat,lon,mode:_goMode,target})
    });
    if(!res.ok){const e=await res.json().catch(()=>({}));throw new Error(e.message||('HTTP '+res.status));}
    const d = await res.json();
    content.innerHTML = d.html;
  } catch(e){
    content.innerHTML = '<div style="color:#b13e3e;padding:24px">⚠ '+(e.message||'Failed to load transit data')+'</div>';
    console.error(e);
  } finally {
    _goLoading = false;
  }
}

</script>
