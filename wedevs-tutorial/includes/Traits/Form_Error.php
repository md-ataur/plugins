<?php
namespace WeDevs\Tutorial\Traits;

trait Form_Error {

    public $errors = [];

    /**
     * Check if the form has error
     *
     * @param  string  $key
     *
     * @return boolean
     */
    public function set_error( $key ) {

        if ( isset( $this->errors[$key] ) ) {
            return $this->errors[$key];
        }

        return false;
    }

    /**
     * Get the error by key
     *
     * @param  key $key
     *
     * @return string | false
     */
    public function get_error( $key ) {

        if ( isset( $this->errors[ $key ] ) ) {
            return $this->errors[ $key ];
        }

        return false;
    }
}
