<?php
// admin/template/footer.php
?>
    </div> <!-- penutup container -->
</div> <!-- penutup main-content -->

<!-- MODAL LOGOUT -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <i class="fas fa-sign-out-alt" style="font-size: 50px; color: #dc3545;"></i>
        <h3>Konfirmasi Logout</h3>
        <p>Apakah Anda yakin ingin keluar?</p>
        <button class="btn-batal" onclick="closeModal()">Batal</button> <br> 
        <button class="btn-logout" onclick="logout()">Ya, Logout</button>
    </div>
</div>

<script>
    function showModal() { document.getElementById('logoutModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('logoutModal').style.display = 'none'; }
    function logout() { window.location.href = '../logout.php'; }
    window.onclick = function(event) {
        let modal = document.getElementById('logoutModal');
        if (event.target == modal) modal.style.display = 'none';
    }
</script>
</body>
</html>