<?php
function isActive(string $page, string $currentPage): string
{
    return $page === $currentPage ? 'active' : '';
}
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
