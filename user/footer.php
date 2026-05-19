<?php
// user/footer.php
?>
    </div> <!-- penutup .container -->
    
    <footer style="background: #1a202c; color: #a0aec0; padding: 30px 20px; text-align: center; margin-top: 50px;">
        <div style="margin-bottom: 15px;">
            <a href="#home" style="color: #a0aec0; text-decoration: none; margin: 0 15px;">Home</a>
            <a href="#menu" style="color: #a0aec0; text-decoration: none; margin: 0 15px;">Menu</a>
            <a href="#contact" style="color: #a0aec0; text-decoration: none; margin: 0 15px;">Contact</a>
            <a href="#tentang" style="color: #a0aec0; text-decoration: none; margin: 0 15px;">Tentang</a>
            
        </div>
        <p>Created by Marthin Lubis | © <?php echo date('Y'); ?> Cafe Boy.</p>
    </footer>
    
    <script>
        function toggleCart() {
            document.getElementById('cartDropdown')?.classList.toggle('show');
        }
        window.onclick = function(e) {
            let dropdown = document.getElementById('cartDropdown');
            if (dropdown && !e.target.closest('.cart-icon')) {
                dropdown.classList.remove('show');
            }
        }
    </script>
</body>
</html>