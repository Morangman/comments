<div class="centered">
    <a href="<?= $this->auth_url . '?' . urldecode(http_build_query($this->auth_params)) ?>">
    <button>Войти через Facebook</button>
    </a>
</div>
<div class="centered">
    <a href="/index.php?action=messages">
    [просмотр сообщений в режиме гостя]
    </a>
</div>