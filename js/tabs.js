// tabs.js - handle menu tabs and persistence
(function(){
  function showTab(tabId){
    const tabs = document.querySelectorAll('.menu-tab');
    const buttons = document.querySelectorAll('.tab-button');
    tabs.forEach(tab => {
      tab.style.display = 'none';
      tab.classList.remove('active');
      tab.classList.remove('showing');
    });
    buttons.forEach(btn => btn.classList.remove('active'));

    const target = document.getElementById(tabId);
    if (target) {
      target.style.display = 'flex';
      if (target.classList.contains('fade-tab')) {
        void target.offsetWidth;
        target.classList.add('showing');
        requestAnimationFrame(() => target.classList.add('active'));
      }
    }
    const btn = document.querySelector(`[onclick="showTab('${tabId}')"]`);
    if (btn) btn.classList.add('active');
    sessionStorage.setItem('activeTab', tabId);
  }
  window.showTab = showTab;

  document.addEventListener('DOMContentLoaded', () => {
    const activeTab = sessionStorage.getItem('activeTab') || 'food';
    showTab(activeTab);
  });
})();
