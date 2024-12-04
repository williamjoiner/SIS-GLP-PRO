<?php

function validateCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1*$/', $cpf)) {
        return false;
    }
    
    // Calcula primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica primeiro dígito
    if ($cpf[9] != $dv1) {
        return false;
    }
    
    // Calcula segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica segundo dígito
    return $cpf[10] == $dv2;
}

function validateCNPJ($cnpj) {
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    // Verifica se tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1*$/', $cnpj)) {
        return false;
    }
    
    // Calcula primeiro dígito verificador
    $soma = 0;
    $multiplicadores = [5,4,3,2,9,8,7,6,5,4,3,2];
    for ($i = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $multiplicadores[$i];
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica primeiro dígito
    if ($cnpj[12] != $dv1) {
        return false;
    }
    
    // Calcula segundo dígito verificador
    $soma = 0;
    $multiplicadores = [6,5,4,3,2,9,8,7,6,5,4,3,2];
    for ($i = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $multiplicadores[$i];
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica segundo dígito
    return $cnpj[13] == $dv2;
}

function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}

function formatCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    return substr($cnpj, 0, 2) . '.' . 
           substr($cnpj, 2, 3) . '.' . 
           substr($cnpj, 5, 3) . '/' . 
           substr($cnpj, 8, 4) . '-' . 
           substr($cnpj, 12, 2);
}

function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $len = strlen($phone);
    
    if ($len == 11) {
        return '(' . substr($phone, 0, 2) . ') ' . 
               substr($phone, 2, 5) . '-' . 
               substr($phone, 7);
    } else if ($len == 10) {
        return '(' . substr($phone, 0, 2) . ') ' . 
               substr($phone, 2, 4) . '-' . 
               substr($phone, 6);
    }
    
    return $phone;
}

function formatZipCode($zip) {
    $zip = preg_replace('/[^0-9]/', '', $zip);
    return substr($zip, 0, 5) . '-' . substr($zip, 5);
}
