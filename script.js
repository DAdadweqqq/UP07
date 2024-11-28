document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        this.classList.add('active');
    });
});
