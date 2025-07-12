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

require_once(dirname(__FILE__).'/similarity_c.class.php');

/**
 * C++ language similarity class
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */
class vpl_similarity_cpp extends vpl_similarity_c {

    /**
     * Returns the type of similarity.
     *
     * @return int The type of similarity, which is 2 for C++.
     */
    public function get_type() {
        return 2;
    }

    /**
     * Returns the tokenizer for the C++ language.
     *
     * @return vpl_tokenizer The tokenizer instance for C++.
     */
    public function get_tokenizer() {
        return vpl_tokenizer_factory::get( 'cpp' );
    }
}
