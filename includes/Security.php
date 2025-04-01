<?php
namespace WorkerIS;

class Security {

    /**
     * Saniert Eingaben rekursiv.
     *
     * @param mixed $input Der zu sanitisierende Wert.
     * @return mixed Der gesäuberte Wert.
     */
    public static function sanitize( $input ) {
        if ( is_string( $input ) ) {
            return sanitize_text_field( $input );
        } elseif ( is_array( $input ) ) {
            return array_map( array( __CLASS__, 'sanitize' ), $input );
        }
        return $input;
    }

    /**
     * Verifiziert einen WordPress-Nonce.
     *
     * @param string $nonce Der zu überprüfende Nonce.
     * @param string $action Der zugehörige Aktion-Name.
     * @return bool True, wenn der Nonce gültig ist, sonst false.
     */
    public static function verify_nonce( $nonce, $action ) {
        return wp_verify_nonce( $nonce, $action ) !== false;
    }
}
