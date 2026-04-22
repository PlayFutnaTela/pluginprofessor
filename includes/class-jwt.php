<?php
/**
 * Validação e manipulação de JWT
 * Autenticação segura de usuários
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_JWT {

    /**
     * Chave secreta para JWT
     */
    private static $secret_key = '';

    /**
     * Algoritmo de criptografia
     */
    private static $algorithm = 'HS256';

    /**
     * Inicializar configurações JWT
     */
    public static function init() {
        // Verificar se WordPress está totalmente carregado
        if (!function_exists('wp_get_current_user')) {
            error_log('[SM-SC-JWT] ⚠️ WordPress ainda não carregado, pulando inicialização JWT');
            return;
        }

        error_log('[SM-SC-JWT] 🔐 Inicializando configurações JWT');

        self::$secret_key = SM_Student_Control_Settings::get('jwt_secret');

        // Gerar chave secreta se não existir
        if (empty(self::$secret_key)) {
            self::$secret_key = wp_generate_password(32, false);
            SM_Student_Control_Settings::set('jwt_secret', self::$secret_key);
            error_log('[SM-SC-JWT] 🔑 Nova chave secreta JWT gerada');
        } else {
            error_log('[SM-SC-JWT] ✅ Chave secreta JWT carregada');
        }
    }

    /**
     * Validar token JWT
     */
    public static function validate_token($token) {
        error_log('[SM-SC-JWT] 🔍 Iniciando validação de token JWT');

        try {
            // Decodificar header
            $header = self::decode_header($token);

            // Verificar algoritmo
            if (!isset($header['alg']) || $header['alg'] !== self::$algorithm) {
                return self::error_response(__('Algoritmo JWT inválido', 'sm-student-control'));
            }

            // Decodificar payload
            $payload = self::decode_payload($token);

            // Verificar expiração
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return self::error_response(__('Token JWT expirado', 'sm-student-control'));
            }

            // Verificar issuer (opcional)
            if (isset($payload['iss']) && $payload['iss'] !== get_site_url()) {
                return self::error_response(__('Issuer JWT inválido', 'sm-student-control'));
            }

            return [
                'valid' => true,
                'payload' => $payload,
            ];

        } catch (Exception $e) {
            return self::error_response(__('Token JWT inválido: ', 'sm-student-control') . $e->getMessage());
        }
    }

    /**
     * Gerar token JWT para usuário
     */
    public static function generate_token($user_id, $expiration_hours = 24) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        $issued_at = time();
        $expiration = $issued_at + ($expiration_hours * 3600);

        $payload = [
            'iss' => get_site_url(), // Issuer
            'sub' => $user_id, // Subject (user ID)
            'iat' => $issued_at, // Issued at
            'exp' => $expiration, // Expiration
            'user' => [
                'id' => $user->ID,
                'login' => $user->user_login,
                'email' => $user->user_email,
                'name' => $user->display_name,
                'roles' => $user->roles,
            ],
        ];

        // Aplicar filtro para modificar payload
        $payload = apply_filters('sm_sc_jwt_payload', $payload, $user);

        return self::encode_token($payload);
    }

    /**
     * Obter dados do professor do token JWT
     */
    public static function get_professor_data() {
        // Verificar se usuário está logado
        if (!is_user_logged_in()) {
            return null;
        }

        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        // Verificar se é professor (ajuste conforme necessário)
        // Por enquanto, assumimos que usuários logados são professores
        // TODO: Implementar verificação de role específica

        return [
            'user_id' => $user_id,
            'school_id' => self::get_user_school_id($user_id),
            'professor_id' => $user_id, // Pode ser diferente dependendo da estrutura
            'name' => $user->display_name,
            'email' => $user->user_email,
        ];
    }

    /**
     * Obter ID da escola do usuário
     * TODO: Implementar baseado na estrutura do LMS
     */
    private static function get_user_school_id($user_id) {
        // Por enquanto, retornar 1 como padrão
        // Deve ser implementado baseado em como o LMS associa usuários a escolas
        return get_user_meta($user_id, 'school_id', true) ?: 1;
    }

    /**
     * Verificar se usuário é professor
     */
    public static function is_professor($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Verificar roles - ajuste conforme necessário
        $professor_roles = apply_filters('sm_sc_professor_roles', ['administrator', 'editor', 'author']);

        return array_intersect($professor_roles, $user->roles) ? true : false;
    }

    /**
     * Decodificar header do JWT
     */
    private static function decode_header($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception(__('Token JWT malformado', 'sm-student-control'));
        }

        $header = json_decode(self::base64url_decode($parts[0]), true);
        if (!$header) {
            throw new Exception(__('Header JWT inválido', 'sm-student-control'));
        }

        return $header;
    }

    /**
     * Decodificar payload do JWT
     */
    private static function decode_payload($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception(__('Token JWT malformado', 'sm-student-control'));
        }

        $payload = json_decode(self::base64url_decode($parts[1]), true);
        if (!$payload) {
            throw new Exception(__('Payload JWT inválido', 'sm-student-control'));
        }

        return $payload;
    }

    /**
     * Codificar token JWT
     */
    private static function encode_token($payload) {
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT',
        ];

        $header_encoded = self::base64url_encode(wp_json_encode($header));
        $payload_encoded = self::base64url_encode(wp_json_encode($payload));

        $signature = self::generate_signature($header_encoded . '.' . $payload_encoded);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature;
    }

    /**
     * Gerar assinatura do token
     */
    private static function generate_signature($data) {
        return self::base64url_encode(
            hash_hmac('sha256', $data, self::$secret_key, true)
        );
    }

    /**
     * Verificar assinatura do token
     */
    private static function verify_signature($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $expected_signature = self::generate_signature($parts[0] . '.' . $parts[1]);

        return hash_equals($expected_signature, $parts[2]);
    }

    /**
     * Base64URL encode
     */
    private static function base64url_encode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64URL decode
     */
    private static function base64url_decode($data) {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * Criar resposta de erro padronizada
     */
    private static function error_response($message) {
        return [
            'valid' => false,
            'error' => $message,
            'payload' => null,
        ];
    }

    /**
     * Testar configuração JWT
     */
    public static function test_jwt_configuration() {
        $token = self::generate_token(get_current_user_id(), 1);

        if (!$token) {
            return [
                'configured' => false,
                'error' => __('Não foi possível gerar token de teste', 'sm-student-control'),
            ];
        }

        $validation = self::validate_token($token);

        return [
            'configured' => $validation['valid'],
            'token_generated' => true,
            'validation_result' => $validation,
        ];
    }
}

// REMOVIDO: SM_Student_Control_JWT::init();