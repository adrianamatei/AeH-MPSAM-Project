<?php
/**
 * Footer pentru toate paginile
 * Închide structurile deschise în header.php și sidebar.php
 */
$_isLoggedIn = isLoggedIn();
?>

<?php if ($_isLoggedIn): ?>
    <footer class="app-footer">
        <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?> &copy; <?= date('Y') ?> 
        | Sistem purtabil de supraveghere a stării de sănătate
    </footer>
    </main><!-- /.app-main -->
</div><!-- /.app-layout -->
<?php else: ?>
    </main><!-- /.app-main-full -->
<?php endif; ?>

<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
