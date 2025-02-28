<?php
// Arquivo temporário para limpar a sessão
session_start();
session_unset();
session_destroy();
echo "Sessão destruída. <a href='/login'>Ir para login</a>";