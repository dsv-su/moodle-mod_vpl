<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/similarity_base.class.php');

/**
 * M (Octave) language similarity class
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */
class vpl_similarity_matlab extends vpl_similarity_base {

    /**
     * Returns the type of similarity.
     *
     * @return int The type of similarity, which is 9 for M (Octave).
     */
    public function get_type() {
        return 9;
    }

    /**
     * Normalizes the syntax of the given tokens.
     *
     * @param array $tokens The tokens to normalize.
     * @return array The normalized tokens.
     */
    public function sintax_normalize(&$tokens) {
        $ret = [];
        foreach ($tokens as $token) {
            if ($token->type == vpl_token_type::OPERATOR) {
                switch ($token->value) {
                    case '[' :
                        // Only add ].
                        break;
                    case '(' :
                        // Only add ).
                        break;
                    case '{' :
                        break;
                    case '<' : // Replace < by >.
                        $token->value = '>';
                        $ret[] = $token;
                        break;
                    case '<=' : // Replace < by >.
                        $token->value = '>=';
                        $ret[] = $token;
                        break;
                    default :
                        $ret[] = $token;
                }
            }
            // TODO remove "(p)" .
        }
        return $ret;
    }

    /**
     * Returns the tokenizer for the Octave language.
     *
     * @return vpl_tokenizer The tokenizer instance for Octave.
     */
    public function get_tokenizer() {
        return vpl_tokenizer_factory::get( 'matlab' );
    }
}
