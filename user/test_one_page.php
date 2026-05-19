<?php
include 'header.php';
?>
<style>
    section { min-height: 100vh; padding: 50px; border-bottom: 2px solid red; }
</style>

<section id="home" style="background:#f0f0f0;">
    <h1>HOME SECTION - BISA DI-LIHAT?</h1>
</section>

<section id="menu" style="background:#fff;">
    <h1>MENU SECTION - BISA DI-LIHAT?</h1>
    <p>Jika Anda melihat ini, section menu berfungsi.</p>
</section>

<section id="contact" style="background:#f0f0f0;">
    <h1>CONTACT SECTION - BISA DI-LIHAT?</h1>
</section>

<section id="tentang" style="background:#fff;">
    <h1>TENTANG SECTION - BISA DI-LIHAT?</h1>
</section>

<script>
// Scroll spy sederhana
window.addEventListener('scroll', function() {
    let sections = document.querySelectorAll('section');
    let navLinks = document.querySelectorAll('.nav-menu a');
    let current = '';
    sections.forEach(section => {
        let top = section.offsetTop - 120;
        if (scrollY >= top) current = section.getAttribute('id');
    });
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) link.classList.add('active');
    });
});
</script>

<?php include 'footer.php'; ?>