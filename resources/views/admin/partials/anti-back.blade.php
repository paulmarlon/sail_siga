<script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (performance.navigation && performance.navigation.type === 2)) {
            window.location.reload(true);
        }
    });
</script>
