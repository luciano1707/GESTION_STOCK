<?php // include/page_end.php ?>
</div><!-- /page-content -->
<?php include '../include/footer.php'; ?>
</div><!-- /main-wrapper -->
</div><!-- /app-layout -->
<script src="../js/script.js"></script>
<script src="../js/crud.js"></script>
<script>
const mq = window.matchMedia('(max-width:768px)');
function checkMobile() {
    const b = document.getElementById('mobileBurger');
    if (b) b.style.display = mq.matches ? 'block' : 'none';
}
mq.addEventListener('change', checkMobile);
checkMobile();
</script>
