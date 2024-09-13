const menuLateral = document.getElementById('menu-lateral');
const hamburger = document.getElementById('hamburger');

// Evento de clique no ícone de hambúrguer
hamburger.addEventListener('click', function() {
    menuLateral.classList.toggle('open');
});

