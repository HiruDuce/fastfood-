// countdown.js - update all .countdown-badge[data-end]
(function(){
  function fmt(t){ return t.toString().padStart(2,'0'); }
  function tick(){
    const nodes = document.querySelectorAll('.countdown-badge[data-end]');
    if(!nodes.length) return;
    const now = new Date();
    nodes.forEach(el=>{
      const raw = (el.getAttribute('data-end')||'').trim();
      if(!raw) return;
      const end = new Date(raw.length<=10 ? `${raw}T23:59:59` : raw);
      const diff = end - now;
      if(diff <= 0){ el.textContent = 'Hết hạn'; el.classList.replace('bg-dark','bg-secondary'); return; }
      const sec = Math.floor(diff/1000);
      const d = Math.floor(sec/86400);
      const h = Math.floor((sec%86400)/3600);
      const m = Math.floor((sec%3600)/60);
      const s = sec%60;
      el.textContent = d>0 ? `Còn ${d}d ${fmt(h)}:${fmt(m)}:${fmt(s)}` : `Còn ${fmt(h)}:${fmt(m)}:${fmt(s)}`;
    });
  }
  tick();
  setInterval(tick, 1000);
})();
