(function(){
  const body = document.body;
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');

  // Toggle sidebar (mobile)
  function toggleSidebar(open){
    if(open === true){ body.classList.add('sidebar-open'); return; }
    if(open === false){ body.classList.remove('sidebar-open'); return; }
    body.classList.toggle('sidebar-open');
  }
  sidebarToggle?.addEventListener('click', ()=> toggleSidebar());
  sidebarBackdrop?.addEventListener('click', ()=> toggleSidebar(false));

  // Dropdown (submenu)
  document.querySelectorAll('.nav-item.has-children > .nav-link[data-toggle="submenu"]').forEach(link=>{
    link.addEventListener('click', (e)=>{
      e.preventDefault();
      const li = link.parentElement;
      const isOpen = li.classList.contains('open');
      // kalau mau accordion, tutup lainnya:
      li.parentElement.querySelectorAll('.nav-item.has-children.open').forEach(x=>{
        if(x!==li) x.classList.remove('open');
      });
      li.classList.toggle('open', !isOpen);
      link.setAttribute('aria-expanded', String(!isOpen));
    });
  });

  // Auto open parent menu kalau halaman aktif (server-side kasih class .active)
  document.querySelectorAll('.submenu .nav-link.active').forEach(a=>{
    const parent = a.closest('.nav-item.has-children');
    if(parent){ parent.classList.add('open'); }
  });
})();
