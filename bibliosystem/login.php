// После успешного входа
echo "<script>
    window.open('../sync.html?action=login&user=" . urlencode(json_encode($user)) . "', 'sync');
</script>";