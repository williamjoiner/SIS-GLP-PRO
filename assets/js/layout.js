document.addEventListener('DOMContentLoaded', function() {
    // Adicionar botão de toggle do menu
    const wrapper = document.querySelector('.wrapper');
    const mobileMenuButton = document.createElement('button');
    mobileMenuButton.className = 'mobile-menu-toggle';
    mobileMenuButton.innerHTML = '<i class="bx bx-menu"></i>';
    document.body.appendChild(mobileMenuButton);

    // Função para gerenciar o menu móvel
    mobileMenuButton.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
        wrapper.classList.toggle('show-sidebar');
        
        // Mudar ícone do botão
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('show')) {
            icon.classList.remove('bx-menu');
            icon.classList.add('bx-x');
        } else {
            icon.classList.remove('bx-x');
            icon.classList.add('bx-menu');
        }
    });

    // Fechar menu ao clicar em um link (mobile)
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                const sidebar = document.querySelector('.sidebar');
                const mobileMenuIcon = document.querySelector('.mobile-menu-toggle i');
                sidebar.classList.remove('show');
                wrapper.classList.remove('show-sidebar');
                mobileMenuIcon.classList.remove('bx-x');
                mobileMenuIcon.classList.add('bx-menu');
            }
        });
    });

    // Fechar menu ao redimensionar a janela
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuIcon = document.querySelector('.mobile-menu-toggle i');
            sidebar.classList.remove('show');
            wrapper.classList.remove('show-sidebar');
            mobileMenuIcon.classList.remove('bx-x');
            mobileMenuIcon.classList.add('bx-menu');
        }
    });
});
