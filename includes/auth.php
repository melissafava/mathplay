<?php
/**
 * Funções auxiliares de autenticação.
 * Todo arquivo protegido deve incluir este arquivo e chamar exigirLogin().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna true se existe um usuário logado na sessão atual.
 */
function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Redireciona para a tela de login caso não exista sessão ativa.
 * Deve ser chamada no topo de qualquer página protegida.
 */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header("Location: login.php");
        exit;
    }
}
